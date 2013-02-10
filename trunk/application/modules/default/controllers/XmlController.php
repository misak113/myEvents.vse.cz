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
    protected $organizationTable;
    protected $authenticateTable;
    protected $categoryTable;
    protected $tagTable;
    
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
            app\models\organizations\OrganizationTable $organizationTable,
            app\models\events\EventTable $eventTable,
            app\models\events\CategoryTable $categoryTable,
            app\models\events\TagTable $tagTable) {
        
        $this->organizationTable = $organizationTable;
        $this->authenticateTable = $authenticateTable;
        $this->eventTable = $eventTable;
        $this->categoryTable = $categoryTable;
        $this->tagTable = $tagTable;
    }

    public function userdataAction() {
        $email = $this->_getParam("email");
        $password = $id = $this->_getParam("password");
        
        $select = $this->authenticateTable->select();
        $select->where("identity = ?", $email);
        $select->where("(verification = ? OR recovered_verification = ?)", $password);
        $select->where("authenticate_provides_id = 1");
        $select->where("active = 1");
        $auth = $this->authenticateTable->fetchRow($select);
        
        if ($auth != null) {
            // Aktualizovat obnovené heslo
            if ($password == $auth->recovered_verification) {
                $auth->verification = $auth->recovered_verification;
                $auth->recovered_verification = null;
                $auth->save();
            } elseif (!empty($auth->recovered_verification) && $password == $auth->verification) {
                $auth->recovered_verification = null;
                $auth->save();
            }
        
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
        $types = explode(",", $this->_getParam("types"));
        $tags = explode(",", $this->_getParam("tags"));
        
        // Create organizations condition
        $orgsInCond = "(";
        $iOrgs = 0;
        foreach ($organizations as $orgId) {
            if ($orgId == "0") {
                continue;
            }
            
            if ($iOrgs != 0) {
                $orgsInCond .= ",";
            }
            $orgsInCond .= (int) $orgId;
            
            $iOrgs++;
        }
        $orgsInCond .= ")";
        
        // Create event types condition
        $eTypesInCond = "(";
        $iETypes = 0;
        foreach ($types as $eTypeId) {
            if ($eTypeId == "0") {
                continue;
            }
            
            if ($iETypes != 0) {
                $eTypesInCond .= ",";
            }
            $eTypesInCond .= (int) $eTypeId;
            
            $iETypes++;
        }
        $eTypesInCond .= ")";
        
        // Create event tags condition
        $eTagsInCond = "(";
        $iETags = 0;
        foreach ($tags as $eTagId) {
            if ($eTypeId == "0") {
                continue;
            }
            
            if ($iETags != 0) {
                $eTagsInCond .= ",";
            }
            $eTagsInCond .= (int) $eTagId;
            
            $iETags++;
        }
        $eTagsInCond .= ")";

        $select = My_Model::get('app\models\events\EventTable')->select();
        $select->setIntegrityCheck(false);
        $select->from("event");
        $select->join(array('oe' => 'organization_own_event'), 'oe.event_id = event.event_id');
        $select->join(array('et' => 'event_has_tag'), 'et.event_id = event.event_id');
        if ($iOrgs != 0) {
            $select->where("oe.organization_id IN " . $orgsInCond);
        }
        if ($iETypes != 0) {
            $select->where("event.category_id IN " . $eTypesInCond);
        }
        if ($iETags != 0) {
            $select->where("et.tag_id IN " . $eTagsInCond);
        }
        $select->where("timeend > NOW()");
        $select->where("active = 1 AND public = 1");
        $select->groupBy("event.event_id");


        $this->template->events = $select->query()->fetchAll();
    }
    
    public function organizationsAction() {
        $this->template->organizations = $this->organizationTable->fetchAll($this->organizationTable->select());
    }
    
    public function eventtypesAction() {
        $this->template->eventTypes = $this->categoryTable->fetchAll($this->categoryTable->select());
    }
    
    public function eventtagsAction() {
        $this->template->eventTags = $this->tagTable->fetchAll($this->tagTable->select());
    }
}

