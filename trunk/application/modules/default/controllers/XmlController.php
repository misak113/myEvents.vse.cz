<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;

/**
 * Controller pro XML data
 *
 */
class XmlController extends BaseController {

    /** @var TitleLoader */
    protected $eventTable;
    protected $organizationOwnEventTable;
    protected $authenticateTable;
    
    const TOKEN_SALT = "9HA7Ekef";
    
    public function init() {
        $this->_helper->layout->disableLayout();
        Nette\Diagnostics\Debugger::$bar = FALSE;
        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=utf-8');
    }

    /**
     * Nastaví kontext contrloleru, Zde se pomocí Dependency Injection vloží do třídy instance služeb, které budou potřeba
     * Mezi služby se řadí také modely a DB modely
     * Je třeba nadefinovat modely v config.neon
     * @param app\services\TitleLoader $titleLoader
     */
    public function setContext(
            app\models\authentication\AuthenticateTable $authenticateTable,
            app\models\organizations\OrganizationOwnEventTable $organizationOwnEventTable,
            app\models\events\EventTable $eventTable) {
        
        $this->organizationOwnEventTable = $organizationOwnEventTable;
        $this->authenticateTable = $authenticateTable;
        $this->eventTable = $eventTable;
    }

    public function userdataAction() {
        $email = $this->_getParam("email");
        $password = $id = $this->_getParam("password");
        
        $select = $this->authenticateTable->select();
        $select->where("identity = ?", $email);
        $select->where("verification = ?", $password);
        $select->where("authenticate_provides_id = 1");
        $select->where("active = 1");
        $auth = $this->authenticateTable->fetchRow($select);
        
        if ($auth != null) {
            $this->template->userExists = true;
            $this->template->userData = $auth->getUser();
            $this->template->organizations = $this->template->userData->getOrganizations();
        } else {
            $this->template->userExists = false;
        }
    }

    public function usersaltAction() {
        $email = $this->_getParam("email");
        $token = $this->_getParam("token");
        
        $select = $this->authenticateTable->select();
        $select->where("identity = ?", $email);
        $select->where("authenticate_provides_id = 1");
        $select->where("active = 1");
        $auth = $this->authenticateTable->fetchRow($select);
        
        if ($auth == null) {
            return;
        }
        
        $explodedEmail = explode("@", $auth->identity);
        $authToken = hash("sha256", $explodedEmail[0] . self::TOKEN_SALT . "@" . $explodedEmail[1]);
        
        if ($token == $authToken) {
            $this->template->salt = My_Password::extractSalt($auth->verification);
        }
    }
    
    public function eventsAction() {
        $organizations = explode(",", $this->_getParam("organizations"));
        
        $inCond = "(";
        $i = 0;
        foreach ($organizations as $orgId) {
            if ($i != 0) {
                $inCond .= ",";
            }
            $inCond .= mysql_real_escape_string($orgId);
            
            $i++;
        }
        $inCond .= ")";

        $select =  My_Model::get('app\models\events\EventTable')->select();
        $select->setIntegrityCheck(false);
        $select->from("event");
        $select->join(array('oe' => 'organization_own_event'), 'oe.event_id = event.event_id');
        $select->where("oe.organization_id IN " . $inCond);
        $select->where("timeend > NOW()");
        $select->where("active = 1 AND public = 1");
        

        $this->template->events = $select->query()->fetchAll();
    }
}

