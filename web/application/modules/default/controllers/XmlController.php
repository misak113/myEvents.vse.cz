<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;
use app\models\authentication\GcmRegistrationTable;

/**
 * Controller pro XML data
 *
 */
class XmlController extends BaseController {

    /** @var TitleLoader */
	/** @var \app\models\events\EventTable */
    protected $eventTable;
	/** @var \app\models\organizations\OrganizationTable */
    protected $organizationTable;
	/** @var \app\models\organizations\OrganizationOwnEventTable */
    protected $organizationOwnEventTable;
	/** @var \app\models\authentication\AuthenticateTable */
    protected $authenticateTable;
	/** @var \app\models\events\CategoryTable */
    protected $categoryTable;
	/** @var \app\models\events\TagTable */
    protected $tagTable;
	/** @var \app\services\GcmMessanger */
    protected $gcmMessanger;
	/** @var \app\models\authentication\GcmRegistrationTable */
	protected $gcmRegistrationTable;
    
    const SALT_TOKEN_SALT = "9HA7Ekef";
    const GCM_TOKEN_SALT = "s8atUbru";
    
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
            app\models\organizations\OrganizationOwnEventTable $organizationOwnEventTable,
            app\models\events\EventTable $eventTable,
            app\models\events\CategoryTable $categoryTable,
            app\models\events\TagTable $tagTable,
			GcmRegistrationTable $gcmRegistrationTable,
            app\services\GcmMessanger $gcmMessanger) {
        
        $this->organizationTable = $organizationTable;
        $this->authenticateTable = $authenticateTable;
        $this->organizationOwnEventTable = $organizationOwnEventTable;
        $this->eventTable = $eventTable;
        $this->categoryTable = $categoryTable;
        $this->tagTable = $tagTable;
        $this->gcmMessanger = $gcmMessanger;
		$this->gcmRegistrationTable = $gcmRegistrationTable;
    }

    public function userdataAction() {
        $email = $this->_getParam("email");
        $password = $id = $this->_getParam("password");
        
        $select = $this->authenticateTable->select();
        $select->where("identity = ?", $email);
        $select->where("(verification = ? OR recovered_verification = ?)", $password);
        $select->where("authenticate_provides_id = 1");
        $select->where("active = 1");
		/** @var \app\models\authentication\Authenticate $auth  */
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
        $authToken = hash("sha256", $explodedEmail[0] . self::SALT_TOKEN_SALT . "@" . $explodedEmail[1]);
        
        if ($token == $authToken) {
            $this->template->salt = My_Password::extractSalt($auth->verification);
        }
    }
    
    public function eventsAction() {
        $organizations = explode(",", $this->_getParam("organizations"));
        $types = explode(",", $this->_getParam("types"));
        
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

		$select = $this->eventTable->select();
        
        $select->setIntegrityCheck(false);
        $select->from("event");
        $select->join(array('oe' => 'organization_own_event'), 'oe.event_id = event.event_id');
        if ($iOrgs != 0) {
            $select->where("oe.organization_id IN " . $orgsInCond);
        }
        if ($iETypes != 0) {
            $select->where("event.category_id IN " . $eTypesInCond);
        }
        $select->where("timeend > NOW()");
        $select->where("active = 1 AND public = 1");
        $select->group("event.event_id");

        $events = $select->query()->fetchAll();
        
        // Orgnaizátoři
        // (tady možná bude časem potřeba trochu optimalizace...protože
        // tenhle způsob práce s DB je dost podivnej a než bych vymyslel,
        // jak se s timhle Netto-Zendím nesmyslem správně pracuje, tak u toho zestárnu...)
        foreach ($events as $index => $event) {
            $select = $this->organizationOwnEventTable->select();
            $select->where("event_id = ?", $event["event_id"]);
            $events[$index]["organizators"] = $this->organizationOwnEventTable->fetchAll($select);
            
            // CRC
            $crcText = $event["name"];
            $crcText .= $event["location"];
            $crcText .= $event["timestart"];
            $crcText .= $event["timeend"];
            $crcText .= $event["longinfo"];
            $crcText .= $event["capacity"];
            $crcText .= $event["url"];
            $crcText .= $event["fburl"];
            foreach ($events[$index]["organizators"] as $organizator) {
                $crcText .= $organizator->organization_id;
            }
            
            $events[$index]["crc"] = crc32($crcText);
        }

        $this->template->events = $events;
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
    
    public function registergcmAction() {
        $authToken = $this->_getParam("authToken");
        $regId = $this->_getParam("regId");
        $status = 1;
        
        // Check auth token
        $checkAuthToken = hash("sha256", self::GCM_TOKEN_SALT . $regId);
        
        if ($checkAuthToken != $authToken) {
            $status = 0;
        } else {
            $gcmRegistration = $this->gcmRegistrationTable->createRow();
            $gcmRegistration->reg_id = $regId;
            $gcmRegistration->save();
        }
        
        $this->template->status = $status;
    }
    
    public function unregistergcmAction() {
        $authToken = $this->_getParam("authToken");
        $regId = $this->_getParam("regId");
        $status = 1;
        
        // Check auth token
        $checkAuthToken = hash("sha256", self::GCM_TOKEN_SALT . $regId);
        
        if ($checkAuthToken != $authToken) {
            $status = 0;
        } else {
            $gcmRegistration = $this->gcmRegistrationTable->getByRegId($regId);
            $gcmRegistration->delete();
        }
        
        $this->template->status = $status;
    }
}

