<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;


 
/*
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 17.11.12
 * Time: 16:39
 * To change this template use File | Settings | File Templates.
 */
class Admin_IndexController extends BaseController {
    
    /** @var TitleLoader */
    protected $titleLoader;
    
    /** @var EventTable */
    protected $eventTable;
    /** @var CategoryTable */
    protected $categoryTable;
    /** @var CategoryTable */
    protected $tagTable;
    
  
    
    /**
     * @param TitleLoader $titleLoader 
     */
    public function setContext(TitleLoader $titleLoader, 
    		app\models\events\EventTable $eventTable,
    		app\models\events\CategoryTable $categoryTable,
    		app\models\events\TagTable $tagTable
    		) {
    	
        $this->titleLoader = $titleLoader;
        $this->eventTable = $eventTable;
        $this->categoryTable = $categoryTable;
        $this->tagTable = $tagTable;
     
    }

    public function loginAction() {
        // Kontrola, zda už uživatel není přihlášen
        if ($this->user->isLoggedIn()) {
            Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector')->gotoRouteAndExit(array(), "eventList");
        }
        
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:login');
        
        // Formulář
        $form = new LoginForm();
        $this->view->loginForm = $form;
    }
    
    public function logoutAction() {
        Zend_Auth::getInstance()->clearIdentity();

        $this->_helper->redirector->gotoRouteAndExit(array(), "userLogin");
    }
    
    public function editAction() {
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:new');
        $record = null;
        
        $eventId = $this->_getParam('id');
        if(!empty($eventId)){
            $record = $this->eventTable->getById($eventId);
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
				
				$userTable = new app\models\authentication\UserTable();
				$organizations = $userTable->getById(Zend_Auth::getInstance()->getIdentity()->user_id)->getOrganizations();
				
				$formValues["organization_id"] = $organizations[0]->organization_id;
				
                $record->updateFromArray($formValues);
                
               
                //TODO flashmessage zmeny ulozeny

                
                $this->_helper->redirector->gotoRoute(
                   array(
                        'module' => 'admin',
                        'controller' => 'index',
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
        $userTable = new app\models\authentication\UserTable();
		$userId = $this->user->getId();
        $user = $userTable->getById($userId);
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
}
