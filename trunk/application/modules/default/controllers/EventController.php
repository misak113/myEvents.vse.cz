<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;
use app\components\Filter\FilterDispatcher;
use app\models\events\EventTable;
use Zette\Diagnostics\TimerPanel;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 15.11.12
 * Time: 22:09
 * To change this template use File | Settings | File Templates.
 */
class EventController extends BaseController {


	/** @var TitleLoader @inject */
	protected $titleLoader;
	/** @var \app\components\Filter\FilterDispatcher @inject */
	protected $filterDispatcher;
        
        /** @var \app\models\events\EventTable @inject */
	protected $eventTable;
        
        protected $dayOfWeek = array(
            0 => 'Neděle',
            1 => 'Pondělí',
            2 => 'Úterý',
            3 => 'Středa',
            4 => 'Čtvrtek',
            5 => 'Pátek',
            6 => 'Sobota'
        );

	/**
	 * Nastaví kontext contrloleru, Zde se pomocí Dependency Injection vloží do třídy instance služeb, které budou potřeba
	 * Mezi služby se řadí také modely a DB modely
	 * Je třeba nadefinovat modely v config.neon
	 * @param app\services\TitleLoader $titleLoader
	 */
	public function setContext(
		TitleLoader $titleLoader
		,FilterDispatcher $filterDispatcher
	) {
		$this->titleLoader = $titleLoader;
		$this->filterDispatcher = $filterDispatcher;
	}


	public function detailAction() {
                $id = $this->_getParam('id');
                $eventRow = $this->eventTable->getEvent((int)$id);
                if (!$eventRow)
                    throw new Zend_Controller_Action_Exception('Akce neexistuje', 404);
                
		$title = $eventRow->name;
		$this->template->title = $title.' - '.$this->t($this->titleLoader->getTitle('Event:detail'));
                
                $this->template->dayOfWeek = $this->dayOfWeek;
                $this->template->date = new DateTime($eventRow->timestart);
                $this->template->event = $eventRow;
                $this->template->sponsors = $eventRow->getSponsors();
                $this->template->organizations = $eventRow->getOrganizations();
                $this->template->tags = $eventRow->getTags();
	}

	/**
	 * Uvodni stranka
	 *
	 */
	public function listAction() {
		TimerPanel::start('listEvents');
                $this->template->dayOfWeek = $this->dayOfWeek;
                
		$this->template->title = $this->t($this->titleLoader->getTitle('Event:list'));

		$filterControl = $this->getComponent('filter');
		$filter = $filterControl->getFilter();
		//if (!$filter) {
		//	$eventDates = $this->eventTable->getEventsThisWeek();
		//} else {
			$eventDates = $this->filterDispatcher->getFilteredEvents($filter);
		//}

		$this->template->eventDates = $eventDates;
		TimerPanel::stop('listEvents');
	}
        
        /**
         * Odesle Json s nazvy eventu pro autocomplete vyhledavani
         */
        public function autocompleteAction() {
            $q = $this->_getParam('q');
            
            $select = $this->eventTable->select();
            $select->where("name like ?", array('%'.$q.'%'))
                    ->limit(20);
            $events = $this->eventTable->fetchAll($select);
            
            $names = array();
            foreach ($events as $event){
                $names[] = $event->name;
            }
            
            $json = Zend_Json::encode($names);
            $this->getResponse()
                ->setHeader('Content-Type', 'text/html')
                ->setBody($json)
                ->sendResponse();

            exit;
        }

	public function createComponentFilter() {
		return $this->filterDispatcher->getFilter();
	}
        
        /******************** Dependency Injection **********************/
	public function injectEventTable(EventTable $eventTable) {
		$this->eventTable = $eventTable;
        }
}
