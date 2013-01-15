<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;

/**
 * Controller pro XML data
 *
 */
class XmlController extends BaseController {

    /** @var TitleLoader */
    protected $userTable;
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
    public function setContext(app\models\authentication\AuthenticateTable $authenticateTable, app\models\authentication\UserTable $userTable) {
        $this->authenticateTable = $authenticateTable;
        $this->userTable = $userTable;
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
        $token = $id = $this->_getParam("token");
        
        $select = $this->authenticateTable->select();
        $select->where("identity = ?", $email);
        $select->where("authenticate_provides_id = 1");
        $select->where("active = 1");
        $auth = $this->authenticateTable->fetchRow($select);
        
        $explodedEmail = explode("@", $auth->identity);
        $authToken = hash("sha256", $explodedEmail[0] . self::TOKEN_SALT . "@" . $explodedEmail[1]);
        
        if ($token == $authToken) {
            $this->template->salt = My_Password::extractSalt($auth->verification);
        }
    }
}

