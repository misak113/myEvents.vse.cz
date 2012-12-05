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
        // ziskame instanci redirector helperu, ktery ma starosti presmerovani
        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');

        $auth = Zend_Auth::getInstance();
        // Stav o autentifikaci uzivatele (prihlaseni) se musi nekde udrzovat, vychozi zpusob je session
        // u session lze nastavit namespace, vychozi je Zend_Auth
        //$auth->setStorage(new Zend_Auth_Storage_Session('My_Auth'));

        $logoutRequest = $request->getParam("logout");
        if (isset($logoutRequest)) { // detekovano odhlaseni
            $auth->clearIdentity();

            // kvuli bezpecnosti provedeme presmerovani
            $redirector->gotoSimpleAndExit($this->failedAction, $this->failedController);
        }

        $loginRequest = $request->getPost("login");
        if (isset($loginRequest)) {
            // Validace (základní verze dokud se mi nepodaří rozchodit validaci přímo z formuláře)
            $email = $request->getPost($this->loginField);
            if (empty($email) || strLen($request->getPost($this->passwordField)) < My_Password::$MIN_LENGTH) {
                $flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
                $flash->clearMessages();
                $flash->addMessage("Některý z přihlašovacích údajů bych zadán chybně");
            } else { // Validace OK
                $db = My\Db\Table::getDefaultAdapter();

                // Zpracování hesla
                $authenticateTable = new app\models\authentication\AuthenticateTable();
                $user = $authenticateTable->fetchRow($this->identityColumn . " = '" . $request->getPost($this->loginField) . "'");

                $password = new My_Password($request->getPost($this->passwordField));
                $password->setSalt(My_Password::extractSalt($user->getVerification()));

                // Nastavení adaptéru
                $adapter = new Zend_Auth_Adapter_DbTable($db, $this->tableName, $this->identityColumn, $this->credentialColumn);
                $adapter->setIdentity($request->getPost($this->loginField));
                $adapter->setCredential($password->getDHash());
                $adapter->getDbSelect()->where("active = 1 AND authenticate_provides_id = 1");

                $auth->authenticate($adapter);


                // Finish
                if ($auth->hasIdentity()) { // Uživatel byl úspěšně ověřen a je přihlášen
                    // Uložit last login data
                    $db->update(
                            "user", array(
                        'last_login_ip' => $request->getServer('REMOTE_ADDR'),
                        'last_login_date' => new Zend_Db_Expr('NOW()'),
                            ), "user_id = '" . $adapter->getResultRowObject()->user_id . "'"
                    );

                    // Přesměrování
                    $redirector->gotoRouteAndExit(array(), $this->successRoute);
                } else { // Neúspěšné přihlášení
                    $flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
                    $flash->clearMessages();
                    $flash->addMessage("Byly zadány špatné přihlašovací údaje");

                    $redirector->gotoRouteAndExit(array(), $this->failRoute);
                }
            }
        }
    }

}