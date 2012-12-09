<?php

use Zette\Services\PluginController;
use app\models\authentication\AuthenticateTable;
use Nette\Security\IUserStorage;
use app\models\authentication\UserTable;

/**
 * Plugin zajistuje autentifikaci uzivatele a presmerovani
 * Nastaveni je prebrano z application.ini s prefixem auth
 *
 * @see Zend_Auth_Adapter_DbTable
 */
class Application_Plugin_DbAuth extends PluginController {

    const AUTHENTICATE_PROVIDE_EMAIL = 1;
    const AUTHENTICATE_PROVIDE_USER = 2;
    
    protected static $ROLE_REDIRECTIONS = array(
        "guest" => "eventList",
        "orgAdmin" => "adminEvents",
        "sysAdmin" => "adminSystem"
    );

    /** @var array */
    private $options;

    /** @var \app\models\authentication\AuthenticateTable @inject */
    protected $authenticateTable;

    /** @var \Zend_Auth */
    protected $auth;

    /** @var \Zend_Auth_Adapter_DbTable */
    protected $authTable;

    /** @var \app\models\authentication\UserTable */
    protected $userTable;

    public function setContext(IUserStorage $userStorage) {
        $this->auth = Zend_Auth::getInstance();
        $this->auth->setStorage($userStorage);
        $this->authTable = new Zend_Auth_Adapter_DbTable($this->connection, $this->tableName, $this->identityColumn, $this->credentialColumn);
    }

    public function injectAuthenticateTable(AuthenticateTable $authenticateTable) {
        $this->authenticateTable = $authenticateTable;
    }

    public function injectUserTable(UserTable $userTable) {
        $this->userTable = $userTable;
    }

    /**
     * Metoda vrátí konkrétní hodnotu z konfigurace
     * Pokud klíč není nalezen, vyhodíme výjimku
     *
     * @param string $key
     * @return mixed
     */
    private function _getParam($key) {
        if (is_null($this->options)) {
            $this->options = Zend_Controller_Front::getInstance()
                    ->getParam('bootstrap')
                    ->getApplication()
                    ->getOptions();
        }

        if (!array_key_exists($key, $this->options['auth'])) {
            throw new Zend_Controller_Exception("Param {auth." . $key . "} not found in application.ini");
        } else {
            return $this->options['auth'][$key];
        }
    }

    /**
     * Wrapper nad metodou _getParam
     * Umozni nam pristupovat ke konfiguraci primo pres $this
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key) {
        return $this->_getParam($key);
    }

    /**
     * PreDespatch
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {

        // Logout
        $logoutRequest = $request->getParam("logout");
        if ($logoutRequest) {
            $this->handleLogout();
            return;
        }

        // Login
        $loginRequest = $request->getPost("login");
        if ($loginRequest) {
            $this->handleLogin();
            return;
        }
    }

    public function handleLogout() {
        $this->auth->clearIdentity();
        $this->redirect("userLogin");
    }

    public function handleLogin() {
        // Data formuláře
        $loginForm = new LoginForm();
        $loginForm->isValid($_POST);
        $loginData = $loginForm->getValues();

        if (!$loginForm->isValid($_POST)) {
            $this->flashMessage("Některý z přihlašovacích údajů bych zadán chybně", self::FLASH_ERROR);
            return;
        }
        // Validace OK
        // Zpracování hesla
        $userAuth = $this->getUserAuth($loginData[$this->loginField]);

        // Kontrola existence autentifikace
        if ($userAuth == null) {
            $this->failLogin();
            return;
        }

        $password = new My_Password($loginData[$this->passwordField]);
        $password->setSalt(My_Password::extractSalt($userAuth->getVerification()));

        // Nastavení adaptéru
        $this->authTable->setIdentity($loginData[$this->loginField]);
        $this->authTable->setCredential($password->getDHash());
        $this->authTable->getDbSelect()
                ->where("authenticate_provides_id IN (?)", array(self::AUTHENTICATE_PROVIDE_EMAIL, self::AUTHENTICATE_PROVIDE_USER)
        );

        $result = $this->auth->authenticate($this->authTable);
        if (!$result->isValid()) {
            $this->failLogin();
        }

        /** @var stdClass $userInfo  */
        $userInfo = $this->authTable->getResultRowObject();
        // Kvůli bezpečnosti smažem heslo
        $userInfo->{$this->credentialColumn} = null;

        // Uživatel nemá aktivní účet
        if (!$userInfo->active) {
            $this->flashMessage("Váš účet není aktivní.", self::FLASH_ERROR);
            //$this->redirect("userLogin"); // @todo jak jsem říkal, nedával bych povinnost mít aktivní mail, jen ho omezit, aby si ho brzy ověřil, ale mohl dále pokračova na stránce
        }

        // Finish
        if ($this->user->isLoggedIn()) { // Neúspěšné přihlášení
            $this->failLogin();
            return;
        }

        // Uživatel byl úspěšně ověřen a je přihlášen
        // Uložit last login data
        $this->connection->update(
                "user", array(
            'last_login_ip' => $this->getRequest()->getServer('REMOTE_ADDR'),
            'last_login_date' => new Zend_Db_Expr('NOW()'),
                ), "user_id = '" . $userInfo->user_id . "'"
        );

        $userInfo->user = $this->userTable->getById($userInfo->user_id);

        // the default storage is a session with namespace Zend_Auth
        /** @var \Zette\Security\UserStorage $authStorage  */
        $authStorage = $this->auth->getStorage();
		$roles = array();
		foreach ($userInfo->user->getRoles() as $role) {
			$roles[] = $role->getUriCode();
		}
        $identity = new \Nette\Security\Identity($userInfo->user_id, $roles, $userInfo);
        $authStorage->setIdentity($identity);
        $authStorage->setAuthenticated(true);


        // Přesměrování podle role
        $role = 'guest';
        $highestLevel = 0;

        foreach ($userInfo->user->getRoles() as $userRole) {
            if (!$userRole) continue;
            if ($userRole['level'] > $highestLevel) {
                $role = $userRole['uri_code'];
                $highestLevel = $userRole['level'];
            }
        }

        $redirection = empty(self::$ROLE_REDIRECTIONS[$role]) ? self::$ROLE_REDIRECTIONS["guest"] : self::$ROLE_REDIRECTIONS[$role];
        $this->redirect($redirection); // Přesměrovat na patřičnou stránku
    }

    /**
     *
     * @param string $userIdentity
     * @return \Zend_Db_Table_Rowset
     */
    protected function getUserAuth($userIdentity) {
        return $this->authenticateTable->fetchRow($this->authenticateTable
                                ->select()
                                ->where($this->identityColumn . " = ?", $userIdentity));
    }

    /**
     * Při nezdaření přihlášení napíše flash a přesměruje
     * není třeba returnovat ?
     */
    protected function failLogin() {
        $this->flashMessage("Byly zadány špatné přihlašovací údaje", self::FLASH_ERROR);
        $this->redirect("userLogin");
    }

}