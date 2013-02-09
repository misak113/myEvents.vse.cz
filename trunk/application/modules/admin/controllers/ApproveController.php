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
    /** @var EventTable */
    protected $eventTable;
    
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
                ->where('ready_to_approve = 1');
                
        if($this->user->isInRole('approver')){
            $where->where('approved is null')
                    ->where('controlled is null');
        }
        if($this->user->isInRole('controller')){
            $where->where('approved is not null')
                   ->where('controlled is null');
        }
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
                    if($this->user->isInRole('approver')){
                        $record->approved = $date;
                    } else if($this->user->isInRole('controller')){
                        $record->controlled = $date;
                    }
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
    
}

?>
