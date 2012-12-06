<?php

use Zette\Services\PluginController;

/**
 * Plugin zajistuje autentifikaci uzivatele a presmerovani
 * Nastaveni je prebrano z application.ini s prefixem auth
 *
 * @see Zend_Auth_Adapter_DbTable
 */
class Application_Plugin_DbAuth extends PluginController {

    private $options;
    private $redirector;

    /**
     * Metoda vrátí konkrétní hodnotu z konfigurace
     * Pokud klíč není nalezen, vyhodíme výjimku
     *
     * @param string $key
     * @return mixed
     */
    private function _getParam($key) {
        if (is_null($this->options)) {
            $this->options = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getApplication()->getOptions();
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
        $this->redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');
        $auth = Zend_Auth::getInstance();

        // Logout
        $logoutRequest = $request->getParam("logout");
        if (isset($logoutRequest)) {
            $auth->clearIdentity();
            $this->redirector->gotoRouteAndExit(array(), $this->failRoute);
        }

        // Login
        $loginRequest = $request->getPost("login");
        if (isset($loginRequest)) {
            // Data formuláře
            $loginForm = new LoginForm();
            $loginForm->isValid($_POST);
            $loginData = $loginForm->getValues();
            
            if (!$loginForm->isValid($_POST)) {
                $flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
                $flash->clearMessages();
                $flash->addMessage("Některý z přihlašovacích údajů bych zadán chybně");
            } else { // Validace OK
                $db = My\Db\Table::getDefaultAdapter();

                // Zpracování hesla
                $authenticateTable = new app\models\authentication\AuthenticateTable();
                $userAuth = $authenticateTable->fetchRow($authenticateTable->select()->where($this->identityColumn . " = ?", $loginData[$this->loginField]));
                
                // Kontrola existence autentifikace
                if ($userAuth == null) {
                    $this->failLogin();
                    return;
                }

                $password = new My_Password($loginData[$this->passwordField]);
                $password->setSalt(My_Password::extractSalt($userAuth->getVerification()));

                // Nastavení adaptéru
                $adapter = new Zend_Auth_Adapter_DbTable($db, $this->tableName, $this->identityColumn, $this->credentialColumn);
                $adapter->setIdentity($loginData[$this->loginField]);
                $adapter->setCredential($password->getDHash());
                $adapter->getDbSelect()->where("active = 1 AND (authenticate_provides_id = 1 OR authenticate_provides_id = 2)");
                
                $auth->authenticate($adapter);
				
                $userInfo = $adapter->getResultRowObject();

                // Finish
                if ($auth->hasIdentity()) { // Uživatel byl úspěšně ověřen a je přihlášen
                    // Uložit last login data
                    $db->update(
                            "user", array(
                        'last_login_ip' => $request->getServer('REMOTE_ADDR'),
                        'last_login_date' => new Zend_Db_Expr('NOW()'),
                            ), "user_id = '" . $adapter->getResultRowObject()->user_id . "'"
                    );
                     
                    
                    // the default storage is a session with namespace Zend_Auth
                   $authStorage = $auth->getStorage();
                   $authStorage->write($userInfo);
                    
                    
                    // Přesměrování
                    $this->redirector->gotoRouteAndExit(array(), $this->successRoute);
                } else { // Neúspěšné přihlášení
                    $this->failLogin();
                }
            }
        }
    }

    private function failLogin() {
        $flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
        $flash->clearMessages();
        $flash->addMessage("Byly zadány špatné přihlašovací údaje");

        $this->redirector->gotoRouteAndExit(array(), $this->failRoute);
    }
}