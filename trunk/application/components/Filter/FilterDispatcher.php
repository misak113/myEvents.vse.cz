<?php
namespace app\components\Filter;

use app\models\events\CategoryTable;
use app\models\organizations\OrganizationTable;
use app\models\events\EventTable;
use app\models\events\TagTable;
use Nette\DateTime;

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
		$select = $this->eventTable->select()->from(array('e'=>'event'))
                        ->where('active = 1')
                        ->where('public = 1')
						->where('timestart >= ?', date('Y-m-d'))
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
_dBar($events);
		return $eventsArray;
	}







	/****************** Protected methods *************************/
	/**
	 * Vytvoří filter komponentu
	 * @return FilterControl
	 */
	protected function createFilter() {
		$categories = $this->categoryTable->getCategories();
		$tags = $this->tagTable->getTags();
		$organizations = $this->organizationTable->getOrganizations();
		$dates = $this->generateDates();

		$filter = new FilterControl();
		$filter->setDates($dates);
		$filter->setCategories($categories);
		$filter->setTags($tags);
		$filter->setOrganizations($organizations);

		return $filter;
	}

	/**
	 * Vygeneruje semestrální i mimosemestrální týdny pro dates filter
	 * @return array
	 */
	protected function generateDates() {
		$weeks = $this->getSemestralWeeks();

		if (empty($weeks)) {
			$from = $this->getDate()->format('W');
			$weeks = $this->getSemestralWeeks(
				$from >= self::WEEK_START_NUMBER_ZIMNI + self::WEEKS_SEMESTRAL_COUNT && $from <= self::WEEK_START_NUMBER_LETNI
					?self::WEEK_START_NUMBER_LETNI
					:self::WEEK_START_NUMBER_ZIMNI
			);
		} else {
			$last = end($weeks);
			$from = ($last['end']->format('W')+1)%self::WEEKS_YEAR_COUNT;
		}
		$weeks += $this->getMimosemestralWeeks($from);

		uasort($weeks, function ($a, $b) {
			return $a['start']->getTimestamp() > $b['start']->getTimestamp() ?1 :-1;
		});

		$weeks = array_slice($weeks, 0, 6, true);

		return $weeks;
	}



	const WEEK_START_NUMBER_LETNI = 39;
	const WEEK_START_NUMBER_ZIMNI = 7;
	const WEEKS_SEMESTRAL_COUNT = 13;
	const WEEKS_YEAR_COUNT = 53;

	/**
	 * Vygeneruje data pro filter a to následující týdny výuky
	 * @return array
	 */
	protected function getSemestralWeeks($weekNow = false) {
		$now = $this->getDate();
		if ($weekNow === false) $weekNow = $now->format('W');
		$firstWeekNumber = false;
		if ($weekNow >= self::WEEK_START_NUMBER_LETNI && $weekNow < self::WEEK_START_NUMBER_LETNI + self::WEEKS_SEMESTRAL_COUNT) {
			$firstWeekNumber = self::WEEK_START_NUMBER_LETNI;
		} elseif ($weekNow >= self::WEEK_START_NUMBER_ZIMNI && $weekNow < self::WEEK_START_NUMBER_ZIMNI + self::WEEKS_SEMESTRAL_COUNT) {
			$firstWeekNumber = self::WEEK_START_NUMBER_ZIMNI;
		}

		// Mimo semestr
		if ($firstWeekNumber === false) {
			return array();
		}

		$weekNumber = $firstWeekNumber;
		$weeks = array();
		for ($schoolWeek = 1;$schoolWeek <= self::WEEKS_SEMESTRAL_COUNT;$schoolWeek++,$weekNumber++) {
			if ($weekNumber >= $now->format('W')) {
				$start = $this->createDateTimeFromWeek($weekNumber);
				$end = $this->createDateTimeFromWeek($weekNumber+1);
				if (!$start || !$end) continue;
				$week = array(
					'id' => $schoolWeek,
					'title' => $schoolWeek.'. Týden ('.$start->format('j.n.').'-'.$end->format('j.n.').')',
					'week_number' => $weekNumber,
					'start' => $start,
					'end' => $end->sub(\DateInterval::createFromDateString('1 days')),
				);
				$weeks[$weekNumber] = $week;
			}
		}

		return $weeks;
	}

	protected function getMimosemestralWeeks($fromWeek) {
		$weeks = array();
		for ($i=self::WEEKS_SEMESTRAL_COUNT+1,$weekNumber = $fromWeek;$i <= self::WEEKS_SEMESTRAL_COUNT+10;$i++,$weekNumber++) {
			$realStart = $weekNumber % self::WEEKS_YEAR_COUNT;
			$realEnd = ($weekNumber+1) % self::WEEKS_YEAR_COUNT;
			$yearStart = $realStart != $weekNumber ?$this->getDate('Y')+1 :$this->getDate('Y');
			$yearEnd = $realEnd != ($weekNumber+1) ?$this->getDate('Y')+1 :$this->getDate('Y');
			$start = $this->createDateTimeFromWeek($realStart, $yearStart);
			$end = $this->createDateTimeFromWeek($realEnd, $yearEnd);
			if (!$start || !$end) continue;
			$week = array(
				'id' => $i,
				'title' => $start->format('j.n.').' - '.$end->format('j.n.'),
				'week_number' => $weekNumber,
				'start' => $start,
				'end' => $end->sub(\DateInterval::createFromDateString('1 days')),
			);
			$weeks[$weekNumber] = $week;
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
		$date = \DateTime::createFromFormat('Y-m-D', $year.'-01-Mon')->sub(\DateInterval::createFromDateString('4 weeks'));
		$limit = 100;
		while($limit > 0) {
			if ($date->format('W') >= $week) {
				return $date;
			}
			$date = $date->add(\DateInterval::createFromDateString('1 weeks'));
			$limit--;
		}

		// Když se něco posere tak aby se nezacyklilo
		return false;
	}


	/**
	 * @param bool $params
	 * @return \Nette\DateTime|string
	 */
	protected function getDate($params = false) {
		$date = new DateTime();
		//$date = \DateTime::createFromFormat('Y-m-d', '2011-03-12');
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
