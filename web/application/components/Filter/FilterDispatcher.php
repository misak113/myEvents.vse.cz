<?php
namespace app\components\Filter;

use app\models\events\CategoryTable;
use app\models\organizations\OrganizationTable;
use app\models\events\EventTable;
use app\models\events\TagTable;
use Nette\DateTime;
use Zette\Diagnostics\TimerPanel;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 30.11.12
 * Time: 20:43
 * To change this template use File | Settings | File Templates.
 */
class FilterDispatcher
{

	/** @var FilterControl */
	protected $filter;

	/** @var \app\models\events\CategoryTable @inject */
	protected $categoryTable;
	/** @var \app\models\events\TagTable @inject */
	protected $tagTable;
	/** @var \app\models\organizations\OrganizationTable @inject */
	protected $organizationTable;
	/** @var \app\models\events\EventTable */
	protected $eventTable;


	/************************* Public methods *********************************/
	/**
	 * Vrátí filter pro vykreslení (pokud neexistuje vytvoří, popřípadě cachuje)
	 * @return FilterControl
	 */
	public function getFilter() {
		if ($this->filter === null) {
			$this->filter = $this->createFilter();
		}

		return $this->filter;
	}

	public function getFilteredEvents($filter) {
		TimerPanel::start('filteredEvents');
		$select = $this->eventTable->select()->from(array('e'=>'event'))
                        ->where('active = 1')
                        ->where('public = 1')
						->where('approved IS NOT NULL')
						->where('controlled IS NOT NULL')
						->where('timestart >= ?', date('Y-m-d'))
                                                ->order('timestart ASC')
                        ;

		// Vyhledávání
		if (isset($filter['search']) && $filter['search'] != ""){
			_dBar('search filtering');
				$search = $filter['search'];
				$select->where("name like ?", array('%'.$search.'%'))
						->orWhere('shortinfo like ?', array('%'.$search.'%'))
						->orWhere('longinfo like ?', array('%'.$search.'%'));
		}
                
                // Filtrace týdnů
		if (isset($filter['date'])) {
			$allDates = $this->generateDates();
			foreach ($filter['date'] as $date_id => $date) {
				$dates[] = "(timestart >= '".$allDates[$date_id]['start']->format('Y-m-d')."' AND timestart <= '".$allDates[$date_id]['end']->format('Y-m-d')."')";
			}
			$select->where(implode(' OR ', $dates)); // @todo nevim jak to stvorit s escapováním
		}

		// Filtrace Kategorií
		if (isset($filter['category'])) {
			foreach ($filter['category'] as $category_id => $category) {
				$categories[] = (int)$category_id;
			}
			$select->where('category_id IN ('.implode(',', $categories).')'); // @todo nevim jak to stvorit s escapováním
		}

		// Filtrace štítků
		if (isset($filter['tag'])) {
			$select->join(array('eht'=>'event_has_tag'), 'eht.event_id = e.event_id', false);
			foreach ($filter['tag'] as $tag_id => $tag) {
				$tags[] = (int)$tag_id;
			}
			$select->where('tag_id IN ('.implode(',', $tags).')'); // @todo nevim jak to stvorit s escapováním
		}
		
		// Filtrace Organizací
		if (isset($filter['organization'])) {
			$select->join(array('ooe'=>'organization_own_event'), 'ooe.event_id = e.event_id', false);
			foreach ($filter['organization'] as $organization_id => $organization) {
				$organizations[] = (int)$organization_id;
			}
			$select->where('organization_id IN ('.implode(',', $organizations).')'); // @todo nevim jak to stvorit s escapováním
		}

		$order = 'timestart';

		$events = $this->eventTable->fetchAll($select, $order);
		$eventsArray = $this->eventTable->createEventDated($events);

		TimerPanel::stop('filteredEvents');
		return $eventsArray;
	}







	/****************** Protected methods *************************/
	/**
	 * Vytvoří filter komponentu
	 * @return FilterControl
	 */
	protected function createFilter() {
		$categories = $this->categoryTable->getCategories();
		$places = $this->tagTable->getPlaces();
		$tags = $this->tagTable->getTags();
		$organizations = $this->organizationTable->getOrganizations();
		$dates = $this->generateDates();

		$filter = new FilterControl();
		$filter->setDates($dates);
		$filter->setCategories($categories);
		$filter->setPlaces($places);
		$filter->setTags($tags);
		$filter->setOrganizations($organizations);

		return $filter;
	}






	const WEEK_START_NUMBER_LETNI = 39;
	const WEEK_START_NUMBER_ZIMNI = 7;
	const WEEKS_SEMESTRAL_COUNT = 13;
	const WEEKS_YEAR_COUNT = 52;

	protected function generateDates() {
		$weeks = array();
		$weekNumber = $this->getDate('W');
		$year = $this->getDate('Y');
		for ($i=0;$i < 7;$i++,$year = $weekNumber+1 >= self::WEEKS_YEAR_COUNT ?$this->getDate('Y')+1 :$this->getDate('Y'),$weekNumber = $weekNumber+1) {
			$realWeekNumber = $weekNumber % self::WEEKS_YEAR_COUNT;
			$start = $this->createDateTimeFromWeek($realWeekNumber, $year);
			$end = $this->createDateTimeFromWeek($realWeekNumber+1, $year)->sub(\DateInterval::createFromDateString('1 days'));
			if (
				($realWeekNumber >= self::WEEK_START_NUMBER_LETNI && $realWeekNumber < self::WEEK_START_NUMBER_LETNI + self::WEEKS_SEMESTRAL_COUNT)
				|| ($realWeekNumber >= self::WEEK_START_NUMBER_ZIMNI && $realWeekNumber < self::WEEK_START_NUMBER_ZIMNI + self::WEEKS_SEMESTRAL_COUNT)
			)
			{
				$schoolWeek =
						$realWeekNumber - self::WEEK_START_NUMBER_ZIMNI < self::WEEKS_SEMESTRAL_COUNT && $realWeekNumber - self::WEEK_START_NUMBER_ZIMNI >= 0
								?$realWeekNumber - self::WEEK_START_NUMBER_ZIMNI+1
								:$realWeekNumber - self::WEEK_START_NUMBER_LETNI+1;
				$week = array(
					'id' => $realWeekNumber,
					'title' => $schoolWeek.'. Týden ('.$start->format('j.n.').'-'.$end->format('j.n.').')',
					'week_number' => $realWeekNumber,
					'start' => $start,
					'end' => $end,
				);
			} else {
				$week = array(
					'id' => $realWeekNumber,
					'title' => $start->format('j.n.').' - '.$end->format('j.n.Y'),
					'week_number' => $realWeekNumber,
					'start' => $start,
					'end' => $end,
				);
			}
			$weeks[$realWeekNumber] = $week;
		}

		return $weeks;
	}

	/**
	 * Podle čísla týdne vrátí první den v daném týdnu resp. jeho DateTime
	 * @param int $week
	 * @return bool|\DateTime
	 */
	protected function createDateTimeFromWeek($week, $year = false) {
		if ($year === false) $year = $this->getDate('Y');

		$limit = 100;

		$date = \DateTime::createFromFormat('Y-m-d', $year.'-01-01');
		while ($date->format('W') != $week) {
			$date = $date->add(\DateInterval::createFromDateString('1 weeks'));
			if (!$limit) break;$limit--;
		}
		while ($date->format('w') != 1) {
			$date = $date->sub(\DateInterval::createFromDateString('1 days'));
			if (!$limit) break;$limit--;
		}

		return $date;
	}


	/**
	 * @param bool $params
	 * @return \Nette\DateTime|string
	 */
	protected function getDate($params = false) {
		$date = new DateTime();
		//$date = \DateTime::createFromFormat('Y-m-d', '2012-12-01');
		if ($params === false) return $date;

		return $date->format($params);
	}




	/******************** Dependency Injection **********************/
	public function injectCategoryTable(CategoryTable $categoryTable) {
		$this->categoryTable = $categoryTable;
	}
	public function injectTagTable(TagTable $tagTable) {
		$this->tagTable = $tagTable;
	}
	public function injectOrganizationTable(OrganizationTable $organizationTable) {
		$this->organizationTable = $organizationTable;
	}
	public function injectEventTable(EventTable $eventTable) {
		$this->eventTable = $eventTable;
	}

}
