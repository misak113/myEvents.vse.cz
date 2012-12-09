<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;
use app\models\authentication\UserTable;
use app\services\facebook\FbImportDispatcher;


 
/*
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 17.11.12
 * Time: 16:39
 * To change this template use File | Settings | File Templates.
 */
class Admin_EventController extends BaseController {
    
    /** @var TitleLoader */
    protected $titleLoader;
    
    /** @var EventTable */
    protected $eventTable;
    /** @var CategoryTable */
    protected $categoryTable;
    /** @var CategoryTable */
    protected $tagTable;
    /** @var \app\models\authentication\UserTable */
	protected $userTable;
	/** @var \app\services\facebook\FbImportDispatcher */
	protected $fbImportDispatcher;
  
    
    /**
     * @param TitleLoader $titleLoader 
     */
    public function setContext(TitleLoader $titleLoader, 
    		app\models\events\EventTable $eventTable,
    		app\models\events\CategoryTable $categoryTable,
    		app\models\events\TagTable $tagTable,
			UserTable $userTable,
			FbImportDispatcher $fbImportDispatcher
    		) {
    	
        $this->titleLoader = $titleLoader;
        $this->eventTable = $eventTable;
        $this->categoryTable = $categoryTable;
        $this->tagTable = $tagTable;
		$this->userTable = $userTable;
		$this->fbImportDispatcher = $fbImportDispatcher;
    }
    
    public function editAction() {
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:new');
        $record = null;
        
        $eventId = $this->_getParam('id');
        if(!empty($eventId)){
            
            $userId = $this->user->getId();
            $user = $this->userTable->getById($userId);
            $organizations = $user->getOrganizations();
            $events = $organizations[0]->getEvents();
            $record = $this->eventTable->getById($eventId);
            
            if (!$record)
                throw new Zend_Controller_Request_Exception('Event does not exist', 404);
                
            $myEvent = false;
            foreach($events as $event) {
                if ($event->event_id == $record->event_id) {
                    $myEvent = true;
                    break;
                }
            }
            if (!$myEvent)
                throw new Zend_Controller_Request_Exception('Not authorized for this event', 404);
        
        }
        
        $form = new EventForm(array(
        		'categories' => $this->categoryTable,
        		'tags' => $this->tagTable
        		));
        $form->setAction($this->_helper->url->url());
        
        if($record !== null) {
            $form->setModifyMode();
        }
        
        if($this->_request->isPost()) {
            if( $form->isValid($this->_request->getPost()) ) {
                $formValues = $form->getValues();
                $this->template->formvalues = $formValues;
                
                // Vytvoření nového řádku v tabulce, pokud ještě neexistuje
                // Jinak očekává v record existující záznam k editaci
				if ($record === null) {
					$record = $this->eventTable->createRow();
				}
				
			
				$formValues["organization_id"] = $organizations[0]->organization_id;
                                
				dump($formValues);
                                dump($form->picture);
                                $stop();
                                
                $record->updateFromArray($formValues);
                
               
                //TODO flashmessage zmeny ulozeny

                
                $this->_helper->redirector->gotoRoute(
                   array(
                        'module' => 'admin',
                        'controller' => 'event',
                        'action' => 'index'
                    ),
                        'default',
                        true
                );
            }
        } else {
            if($record !== null){
            	
            	$data = $record->toArray(array('tags' => true));
            	$datestart = new Zend_Date($data["timestart"]);
                $data["date"] = $datestart->toString('YYYY-MM-dd');
            	$data["timestart"] = $datestart->toString('HH:mm');
            	
				if ($data["timeend"]) {            	
            		$dateend = new Zend_Date($data["timeend"]);
            		$data["timeend"] = $dateend->toString('HH:mm');
				}
				
				$data["category"] = $data["category_id"];
				
            	$form->populate($data);
            }
        }
        
        
        $this->template->form = $form;
    }
    
    public function indexAction() {
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:index');
        // $this->template->events = $this->eventTable->fetchAll();

        // Seženeme si jeho objekt a přes něj zjistíme, jakých organizací je členem
		$userId = $this->user->getId();
        $user = $this->userTable->getById($userId);
        $organizations = $user->getOrganizations();
       
        // Pokud je uživatel členem nějaké organizace
        if (count($organizations) > 0) {
    	    // Pošleme do view akce první organizace, které je členem
	        // Systém tedy zatím umožňuje uživateli správu jen jedné organizace
        	// TODO: Umožnit správu více organizací
        	$this->template->events = $organizations[0]->getEvents();
        	$this->template->nazevOrganizace = $organizations[0]->name;
       	// Uživatel není členem organizace, dummy výpis
        } else {
        	$this->template->nazevOrganizace = "Nejste členem žádné organizace";
        	$this->template->events  = "";
        }
        
    }


	public function fbImportAction() {
		$this->template->title = $this->titleLoader->getTitle('Admin:Event:fbImport');

		$userId = $this->user->getId();
		$user = $this->userTable->getById($userId);
		$organizations = $user->getOrganizations();
		if ($organizations->count() == 0) {
			$this->flashMessage('Nejste správcem žádné z organizací', self::FLASH_ERROR);
			$this->redirect('adminEvents');
		}
		$events = $this->fbImportDispatcher->importEventsByOrganization($organizations->current());

		if (!$events) {
			$this->flashMessage('Při importování FB akcí došlo k chybě', self::FLASH_ERROR);
			$this->redirect('adminEvents');
		}

		$this->flashMessage('Bylo přidáno '.count($events).' z Facebooku.');
		$this->redirect('adminEvents');
	}

	//public function redirect($url, array $prm = array()) {}
}
