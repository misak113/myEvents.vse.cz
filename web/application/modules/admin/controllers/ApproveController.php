<?php
use Zette\UI\BaseController;
use app\services\TitleLoader;

/**
 * ApproveController se stara o vse kolem schvalovani akci ze strany skoly
 *
 * @author Fandič
 */
class Admin_ApproveController extends BaseController {
    
    /** @var TitleLoader */
    protected $titleLoader;
    /** @var \app\models\events\EventTable */
    protected $eventTable;


	public function init() {
		$this->_helper->layout->setLayout('admin_approve');
	}
    
    /**
     * @param TitleLoader $titleLoader 
     */
    public function setContext(TitleLoader $titleLoader, app\models\events\EventTable $eventTable){
        $this->titleLoader = $titleLoader;
        $this->eventTable = $eventTable;
    }
    
    public function readytoapproveAction () {
        //TODO zaslat email pro approvers
        if($this->_request->isPost()){
            $eventId = $this->_getParam('id');
            $ready = $this->_getParam('ready');
            
            if(!empty($eventId) && isset($ready)){
                $record = $this->eventTable->getById($eventId);
                $record->ready_to_approve = 1;
                $record->save();
            }
            
            $this->_helper->redirector->gotoRoute(array('module' => 'admin',
                                                        'controller' => 'event',
                                                        'action' => 'index'), 
                                                          'default', 
                        true);
        }
    }
    
    public function listAction() {
        //TODO overit prava
        barDump($this->user->getRoles());
        barDump($this->user);
        $this->template->title = $this->titleLoader->getTitle('Admin:Approve:list');

        $where = $this->eventTable->select()
                ->where('active = 1')
                ->where('ready_to_approve = 1')
				->where('timeend > NOW()')
				->order('timestart');

			$where->where('(0');
        if($this->user->isAllowed('admin.approve', 'approve')){
            $where->orWhere('approved is null');
        }
        if($this->user->isAllowed('admin.approve', 'control')){
            $where->orWhere('controlled is null');
        }
		$where->orWhere('0)');

		barDump($where);
        $events = $this->eventTable->fetchAll($where);
        
        $this->template->events = $events;
    }
    
    public function approveAction() {
        if($this->_request->isPost()){
            $eventId = $this->_getParam('id');
            
            if(!empty($eventId)){
                $record = $this->eventTable->getById($eventId);
                
                if($record){
                    $date = date('Y-m-d H:i:s', time());
                    $record->approved = $date;
                    $record->save();
                    $this->flashMessage("Událost " . $record->name. " byla schválena.", self::FLASH_INFO);
                }
            }
        }
        $this->_helper->redirector->gotoRoute(array('module' => 'admin',
                                                        'controller' => 'approve',
                                                        'action' => 'list'), 
                                                        'default', 
                        true);
    }
	public function controlAction() {
		if($this->_request->isPost()){
			$eventId = $this->_getParam('id');

			if(!empty($eventId)){
				$record = $this->eventTable->getById($eventId);

				if($record){
					$date = date('Y-m-d H:i:s', time());
					$record->controlled = $date;
					$record->save();
					$this->flashMessage("Událost " . $record->name. " byla označena jako zkontrolována.", self::FLASH_INFO);
				}
			}
		}
		$this->_helper->redirector->gotoRoute(array('module' => 'admin',
				'controller' => 'approve',
				'action' => 'list'),
			'default',
			true);
	}
    
}

?>
