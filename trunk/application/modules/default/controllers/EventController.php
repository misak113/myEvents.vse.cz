<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;
use app\components\Filter\FilterDispatcher;
use app\models\events\EventTable;

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
                barDump($id);
                $eventRow = $this->eventTable->getById((int)$id);
                barDump($eventRow);
                barDump($this->_getParam('bla'));
                if (!$eventRow)
                    throw new Zend_Controller_Action_Exception('Akce neexistuje', 404);
                
		$title = $eventRow->name;
		$this->template->title = $title.' - '.$this->t($this->titleLoader->getTitle('Event:detail'));
                
                
                $this->template->event = $eventRow;
                $this->template->sponsors = $eventRow->getSponsors();
                $this->template->organizations = $eventRow->getOrganizations();
	}

	/**
	 * Uvodni stranka
	 *
	 */
	public function listAction() {
                $this->template->dayOfWeek = array(
                    0 => 'Neděle',
                    1 => 'Pondělí',
                    2 => 'Úterý',
                    3 => 'Středa',
                    4 => 'Štvrtek',
                    5 => 'Pátek',
                    6 => 'Sobota'
                );
                
		$this->template->title = $this->t($this->titleLoader->getTitle('Event:list'));
                $this->template->eventDates = $this->eventTable->getEventsThisWeek();

	}

	public function createComponentFilter() {
		return $this->filterDispatcher->createComponentFilter();
	}
        
        /******************** Dependency Injection **********************/
	public function injectEventTable(EventTable $eventTable) {
		$this->eventTable = $eventTable;
        }
}
