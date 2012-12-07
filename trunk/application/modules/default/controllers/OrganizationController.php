<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;
use app\models\organizations\OrganizationTable;

/**
 * Organization Controller
 */
class OrganizationController extends BaseController {


	/** @var TitleLoader @inject */
	protected $titleLoader;
        
        /** @var \app\models\events\OrganizationTable @inject */
	protected $organizationTable;

	/**
	 * Nastaví kontext contrloleru, Zde se pomocí Dependency Injection vloží do třídy instance služeb, které budou potřeba
	 * Mezi služby se řadí také modely a DB modely
	 * Je třeba nadefinovat modely v config.neon
	 * @param app\services\TitleLoader $titleLoader
	 */
	public function setContext(
		TitleLoader $titleLoader
	) {
		$this->titleLoader = $titleLoader;
	}


	public function detailAction() {
                $id = $this->_getParam('id');
                $organizationRow = $this->organizationTable->getById((int)$id);
                
                if (!$organizationRow)
                    throw new Zend_Controller_Action_Exception('Organizace neexistuje', 404);
                
		$title = $organizationRow->name;
		$this->template->title = $title.' - '.$this->t($this->titleLoader->getTitle('Organization:detail'));
                
                
                $this->template->organization = $organizationRow;
                $this->template->events = $organizationRow->getEvents();
	}

	/**
	 * Vypis organizaci
	 *
	 */
	public function listAction() {
        
	}

        
        /******************** Dependency Injection **********************/
	public function injectOrganizationTable(OrganizationTable $organizationTable) {
		$this->organizationTable = $organizationTable;
        }
}
