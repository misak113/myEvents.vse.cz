<?php

/**
 * Plugin zajistuje autentifikaci uzivatele a presmerovani
 * Nastaveni je prebrano z application.ini s prefixem auth
 *
 * @see Zend_Auth_Adapter_DbTable
 */
class Application_Plugin_DbAuth extends Zend_Controller_Plugin_Abstract {

    /**
     *
     * @var array
     */
    private $_options;

    /**
     * Metoda vrati konkretni hodnotu z konfigurace
     * Pokud klic neni nalezen, vyhodime vyjimku
     *
     * @param string $key
     * @return mixed
     */
    private function _getParam($key) {
        if (is_null($this->_options)) {
            $this->_options = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getApplication()->getOptions();
        }

        if (!array_key_exists($key, $this->_options['auth'])) {
            throw new Zend_Controller_Exception("Param {auth.$key} not found in application.ini");
        } else {
            return $this->_options['auth'][$key];
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
     * Enter description here...
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

            $db = My\Db\Table::getDefaultAdapter();

            // Vytvarime instance adapteru pro autentifikaci
            // nastavime parametry podle naseho nazvu tabulky a sloupcu
            // treatment obsahuje pripadne pouzitou hashovaci funkci pro heslo
            $adapter = new Zend_Auth_Adapter_DbTable($db, $this->tableName, $this->identityColumn, $this->credentialColumn);

            // jmeno a heslo predame adapteru
            $adapter->setIdentity($request->getPost($this->loginField));
            $adapter->setCredential(hash("sha1", $request->getPost($this->passwordField)));
            $adapter->getDbSelect()->where("active = 1 AND authenticate_provides_id = 1");

            // obecny proces autentifikace s libovolnym adapterem
            $result = $auth->authenticate($adapter);

            if ($auth->hasIdentity()) { // Uživatel byl úspěšně ověřen a je přihlášen


                /* $identity = $auth->getIdentity();

                  // identity obsahuje v nasem pripade ID uzivatele z databaze
                  // muzeme napr. ulozit IP adresu, cas posledniho prihlaseni atd.

                  $db->update($this->tableName, array(
                  'lognum' => new Zend_Db_Expr('lognum + 1'),
                  'ip' => $request->getServer('REMOTE_ADDR'),
                  'last_login' => new Zend_Db_Expr('NOW()'),
                  'browser' => $request->getServer('HTTP_USER_AGENT')),
                  $this->identityColumn . " = '$identity'"); */


                // Přesměrování
                $redirector->gotoRouteAndExit(array(), $this->successRoute);
            } else {
                // autentifikace byla neuspesna
                // FlashMessenger slouzi k uchovani zprav v session
                $flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
                $flash->clearMessages();

                // vlozime do session rovnou chybove hlasky, ktere pak predame do view
                foreach ($result->getMessages() as $msg) {
                    $flash->addMessage($msg);
                }

                /*
                  // nicmene muzeme je nastavit podle konkretniho chyboveho kodu

                  if ($result == Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID)
                  {
                  // neplatne heslo
                  }
                  else if ($result == Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS)
                  {
                  // nalezeno vice uzivatelskych identit
                  }
                  else if ($result == Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND)
                  {
                  // identita uzivatele nenalezena
                  }
                 *
                 */

                $redirector->gotoRouteAndExit(array(), $this->failRoute);
            }
        }
    }

}