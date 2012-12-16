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
		$select = $this->eventTable->select()->from(array('e'=>'event'));

		// Vyhledávání
                if (isset($filter['search']) && $filter['search'] != ""){
                        $search = mysql_real_escape_string($filter['search']);
                        $select->where('name like \'%'.$search.'%\'')
                                ->orWhere('shortinfo like \'%'.$search.'%\'')
                                ->orWhere('longinfo like \'%'.$search.'%\'');
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
	 * Vygeneruje data pro filter a to následující týdny výuky
	 * @return array
	 */
	protected function generateDates() {
		$now = new DateTime();
		$weekNow = $now->format('W');
		$firstWeekNumber = 1;
		if ($weekNow >= 39 && $weekNow <= 51) {
			$firstWeekNumber = 39;
		} elseif ($weekNow >= 7 && $weekNow <= 19) {
			$firstWeekNumber = 7;
		}

		$weekNumber = $firstWeekNumber;
		$weeks = array();
		for ($schoolWeek = 1;$schoolWeek <= 13;$schoolWeek++,$weekNumber++) {
			if ($weekNumber >= $now->format('W')) {
				$start = $this->createDateTimeFromWeek($weekNumber);
				$end = $this->createDateTimeFromWeek($weekNumber+1)->sub(\DateInterval::createFromDateString('1 days'));
				$week = array(
					'id' => $schoolWeek,
					'title' => $schoolWeek.'. Týden ('.$start->format('j.n.').'-'.$end->format('j.n.').')',
					'week_number' => $weekNumber,
					'start' => $start,
					'end' => $end,
				);
				$weeks[$schoolWeek] = $week;
			}
		}

		return $weeks;
	}

	/**
	 * Podle čísla týdne vrátí první den v daném týdnu resp. jeho DateTime
	 * @param int $week
	 * @return bool|\DateTime
	 */
	protected function createDateTimeFromWeek($week) {
		$date = \DateTime::createFromFormat('Y-m-D', date('Y').'-01-Mon');
		$limit = 100;
		while($limit > 0) {
			if ($date->format('W') >= $week) {
				return $date;
			}
			$date = $date->add(\DateInterval::createFromDateString('1 weeks'));
			$limit--;
		}
		return false;
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
