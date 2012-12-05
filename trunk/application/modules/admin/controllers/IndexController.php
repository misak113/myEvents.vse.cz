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
    
    
    /**
     * @param TitleLoader $titleLoader 
     */
    public function setContext(TitleLoader $titleLoader, 
    		app\models\events\EventTable $eventTable,
    		app\models\events\CategoryTable $categoryTable) {
        $this->titleLoader = $titleLoader;
        $this->eventTable = $eventTable;
        $this->categoryTable = $categoryTable;
    }

    public function loginAction() {
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:login');
        
        // Formulář
        $form = new LoginForm();
        $this->view->loginForm = $form;
    }
    
    public function logoutAction() {
        Zend_Auth::getInstance()->clearIdentity();

        $this->_helper->redirector->gotoRouteAndExit(array(), "adminLogin");
    }
    
    public function editAction() {
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:new');
        $record = null;
        
        $eventId = $this->_getParam('id');
        if(!empty($eventId)){
            $record = $this->eventTable->getById($eventId);
        }
        
        $form = new EventForm(array('categories' => $this->categoryTable));
        $form->setAction($this->_helper->url->url());
        
        if($record !== null) {
            $form->setModifyMode();
        }
        
        if($this->_request->isPost()) {
            if( $form->isValid($this->_request->getPost()) ){
                $formValues = $form->getValues();
                $this->template->formvalues = $formValues;
                
                // Vytvoření nového řádku v tabulce, pokud ještě neexistuje
                // Jinak očekává v record existující záznam k editaci
				if ($record === null) {
					$record = $this->eventTable->createRow();
				}
				
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
            	
            	$data = $record->toArray();
            	$datestart = new Zend_Date($data["timestart"]);
            	$data["date"] = $datestart->toString('dd.MM.YYYY');
            	$data["timestart"] = $datestart->toString('HH:mm');
            	
				if ($data["timeend"]) {            	
            		$dateend = new Zend_Date($data["timeend"]);
            		$data["timeend"] = $dateend->toString('HH:mm');
				}
				
				$data["category"] = $data["category_id"];
				
				if(get_magic_quotes_gpc()) {
					
					$data["shortinfo"] = stripslashes($data["shortinfo"]);
					$data["longinfo"] = stripslashes($data["longinfo"]);
					
				}
            	$form->populate($data);
            }
        }
        
        
        $this->template->form = $form;
    }
    
    public function indexAction() {
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:index');
        $this->template->events = $this->eventTable->fetchAll(); //TODO nacitat jen akce dane organizace
        $this->template->nazevOrganizace = "CahFlow Club";
    }
}
