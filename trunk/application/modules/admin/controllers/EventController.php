<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;
use app\models\authentication\UserTable;
use app\services\facebook\FbImportDispatcher;
use app\services\facebook\FbExportDispatcher;
use app\models\authentication\User;

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

    /** @var \app\models\events\EventTable */
    protected $eventTable;

    /** @var \app\models\events\CategoryTable */
    protected $categoryTable;

    /** @var \app\models\events\CategoryTable */
    protected $tagTable;

    /** @var \app\models\authentication\UserTable */
    protected $userTable;

    /** @var \app\services\facebook\FbImportDispatcher */
    protected $fbImportDispatcher;
	/** @var \app\services\facebook\FbExportDispatcher */
	protected $fbExportDispatcher;
    
    /** @var \app\models\events\ClassroomTable */
    protected $classroomtable;
    
    public function init() {
        $this->_helper->layout->setLayout('admin_org');
    }

    /**
     * @param TitleLoader $titleLoader 
     */
    public function setContext(
            TitleLoader $titleLoader,
            app\models\events\EventTable $eventTable,
            app\models\events\CategoryTable $categoryTable,
            app\models\events\TagTable $tagTable,
            UserTable $userTable,
            FbImportDispatcher $fbImportDispatcher,
            app\models\events\ClassroomTable $classroomtable,
            FbExportDispatcher $fbExportDispatcher
	) {

        $this->titleLoader = $titleLoader;
        $this->eventTable = $eventTable;
        $this->categoryTable = $categoryTable;
        $this->tagTable = $tagTable;
        $this->userTable = $userTable;
        $this->fbImportDispatcher = $fbImportDispatcher;
        $this->fbExportDispatcher = $fbExportDispatcher;
        $this->classroomtable = $classroomtable;
    }
    
    public function autocompleteclassroomsAction() {
        
        $classrooms = $this->classroomtable->getClassrooms();
        echo Zend_Json::encode($classrooms);
        exit();

    }
    
    public function handleuploadAction() {
        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array("png","jpg","jpeg","gif","bmp");
        // max file size in bytes
        $sizeLimit = 10 * 1024 * 1024;
        
        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        
        $dir = "img/picture";
        
        $result = $uploader->handleUpload($dir);
        
        $image = \Nette\Image::fromFile($dir . "/" . $result['filename']);
        $image->resize(269, 200, \Nette\Image::EXACT);
        $image->save($dir . "/" . $result['filename']);
        
        // to pass data through iframe you will need to encode all html tags
        echo htmlspecialchars(Zend_Json::encode($result), ENT_NOQUOTES);
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }
    
    public function editAction() {
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:new');
        $this->template->formHeader = "Nová akce";
        $record = null;

		$organizationId = $this->_getParam('organization_id');
		$eventId = $this->_getParam('id');
        if (!empty($eventId)) {

            $userId = $this->user->getId();
			/** @var User $user  */
            $user = $this->userTable->getById($userId);
            $organizations = $user->getOrganizations();
			$events = array();
			foreach ($organizations as $organization) {
				/** @var \app\models\organizations\Organization $organization  */
            	$events = array_merge($organization->getAllEvents()->toArray(), $events);
			}
			/** @var \app\models\events\Event $record  */
			$record = $this->eventTable->getById($eventId);

            if (!$record)
                throw new Zend_Controller_Request_Exception('Event does not exist', 404);

			$eventIds = array_map(function ($e) {
				return $e['event_id'];
			}, $events);

            if (!in_array($record->event_id, $eventIds)) {
                throw new Zend_Controller_Request_Exception('Not authorized for this event', 404);
			}
        }

        $form = new EventForm(array(
                    'categories' => $this->categoryTable,
                    'tags' => $this->tagTable
                ));
        $form->setAction($this->_helper->url->url());

        if ($record !== null) {
            $this->template->formHeader = "Editace akce";
            $form->setModifyMode();
        }

        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $formValues = $form->getValues();
                $this->template->formvalues = $formValues;

                // Vytvoření nového řádku v tabulce, pokud ještě neexistuje
                // Jinak očekává v record existující záznam k editaci
                if ($record === null) {
                    $record = $this->eventTable->createRow();
                }
                
                $userId = $this->user->getId();
                $user = $this->userTable->getById($userId);
                $organizations = $user->getOrganizations()->toArray();
				// Kontrola zda je správná organizace, do které přidává
				if ($organizationId) {
					$formValues["organization_id"] = $organizationId;
				}
				$organizationIds = array_map(function ($a) {
					return $a['organization_id'];
				}, $organizations);
				if (isset($formValues["organization_id"]) && !in_array($formValues["organization_id"], $organizationIds)) {
					$this->flashMessage('Do této organizace nesmíte přidávat události', self::FLASH_ERROR);
					$this->redirect('adminEvents');
					return;
				}
                $record->updateFromArray($formValues);

                $this->flashMessage("Změny v události uloženy.");
                
                $this->_helper->redirector->gotoRoute(
                        array(
                    'module' => 'admin',
                    'controller' => 'event',
                    'action' => 'index'
                        ), 'default', true
                );
            }
        } else {
            if ($record !== null) {

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
		$this->template->organizations = $organizations;
    }

    public function neareventsAction() {
        $this->_helper->layout->disableLayout();
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:nearEvents');

        $datetime = $this->_getParam('datetime'); //TODO kontrola validity
        $datetime = preg_replace('/%3A/', ':', $datetime);
        $events = $this->eventTable->getNearEvents($datetime);

        $this->template->events = $events;
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
		$events = array();
		foreach ($organizations as $organization) {
			try {
				$eventsOrg = $this->fbImportDispatcher->importEventsByOrganization($organization);
				if (is_array($eventsOrg)) {
					$events = array_merge($eventsOrg, $events);
				}
			} catch (FacebookApiException $e) {
				if (strstr($e->getMessage(), 'access token') !== false) { // Pokud je to chyba s access_tokenem
					$this->flashMessage('Pro import z FB je třeba být přihlášen pomocí Facebooku', self::FLASH_ERROR);
				} else {
					//_dBar($e);die(); // @todo logovat
					$this->flashMessage('Při importování došlo k chybě', self::FLASH_ERROR);
				}
				//$this->redirect('adminEvents');
			}
		}

        if (!$events) {
            $this->flashMessage('Žádná akce nebyla importována', self::FLASH_INFO);
            $this->redirect('adminEvents');
        } else {
            $this->flashMessage('Byly přidány ' . count($events) . ' akce z Facebooku.');
            $this->redirect('adminEvents');
        }
    }
	public function fbExportAction() {
		$this->template->title = $this->titleLoader->getTitle('Admin:Event:fbExport');

		$userId = $this->user->getId();
		$user = $this->userTable->getById($userId);
		$organizations = $user->getOrganizations();
		if ($organizations->count() == 0) {
			$this->flashMessage('Nejste správcem žádné z organizací', self::FLASH_ERROR);
			$this->redirect('adminEvents');
		}
		try {
			$events = $this->fbExportDispatcher->exportEventsToOrganization($organizations->current());
		} catch (FacebookApiException $e) {
			if (strstr($e->getMessage(), 'access token') !== false) { // Pokud je to chyba s access_tokenem
				$this->flashMessage('Pro import z FB je třeba být přihlášen pomocí Facebooku', self::FLASH_ERROR);
			} else {
				$this->flashMessage('Při importování došlo k chybě', self::FLASH_ERROR);
			}
			$this->redirect('adminEvents');
		}

		if (!$events) {
			$this->flashMessage('Žádná akce nebyla exportována', self::FLASH_INFO);
			$this->redirect('adminEvents');
		}

		$this->flashMessage('Byly přidány ' . count($events) . ' akce z Facebooku.');
		$this->redirect('adminEvents');
	}

    //public function redirect($url, array $prm = array()) {}
    
    
    /**
     * Nastavi v DB atribut active=0
     */
    public function deleteAction(){
        if($this->_request->isPost()){
            $eventId = $this->_getParam('id');
            
            if(!empty($eventId)){
                $record = $this->eventTable->getById($eventId);
                
                if($record){
                    $record->active = 0;
                    $record->save();
                    $this->flashMessage("Událost " . $record->name. " byla odstraněna.", self::FLASH_INFO);
                }
            }
        }
        $this->_helper->redirector->gotoRoute(array('module' => 'admin',
                                                        'controller' => 'event',
                                                        'action' => 'index'), 
                                                        'default', 
                        true);
    }
    
    public function publishAction() {
        if($this->_request->isPost()){
            $eventId = $this->_getParam('id');
            $public = $this->_getParam('public');
            
            if(!empty($eventId) && isset($public)){
                $record = $this->eventTable->getById($eventId);
                $this->setPublic($record, $public);
            }
        }
        
        $this->_helper->redirector->gotoRoute(array('module' => 'admin',
                                                        'controller' => 'event',
                                                        'action' => 'index'), 
                                                          'default', 
                        true);
    }
    
    protected function setPublic($record, $public) {
        if($record){
            $record->public = $public;
            $record->setPublicityChanged(true);
            $record->save();
            if($public){
                $this->flashMessage("Událost " . $record->name. " byla publikována.", self::FLASH_INFO);
            } else {
                $this->flashMessage("Událost " . $record->name. " byla skryta.", self::FLASH_INFO);
            }
        }
    }
}
