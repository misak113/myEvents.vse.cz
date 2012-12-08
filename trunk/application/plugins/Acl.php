<?php

use Zette\Services\PluginController;
use app\models\authentication\UserTable;

/**
 * Plugin resi autorizaci uzivatele
 * Pokud uzivatel nema pristup do vybraneho controlleru/akce
 * je presmerovan na prihlasovaci obrazovku
 * do messengeru je pridana chybova hlaska
 */
class Application_Plugin_Acl extends PluginController {

	/** @var UserTable @inject */
	protected $userTable;

	public function injectUserTable(UserTable $userTable) {
		$this->userTable = $userTable;
	}

    /**
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        $options = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getApplication()->getOptions();

        $config = new Zend_Config($options);

        $acl = new My_Acl($config);

        $role = 'guest';

        if ($this->user->isLoggedIn()) { // @todo
            $highestLevel = 0;

            foreach ($this->user->getRoles() as $userRole) {
				if (!$userRole) continue;
                if ($userRole['level'] > $highestLevel) {
                    $role = $userRole['uri_code'];
                    $highestLevel = $userRole['level'];
                }
            }
        }

        $controller = $request->getControllerName();
        $action = $request->getActionName();

        $resource = $controller;
        $privilege = $action;

        if (!$acl->has($resource)) {
            $resource = null;
        }

        if (is_null($privilege)) {
            $privilege = 'index';
        }

        // Přístup nepovolen
        if ($request->getModuleName() == "admin" && !$acl->isAllowed($role, $resource, $privilege)) {

            $flash = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
            $flash->clearMessages();


            $flash->addMessage('Nejste přihlášen nebo nemáte patřičné oprávnění');


            $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');
            $redirector->gotoRouteAndExit(array(), $this->user->isLoggedIn() ? "eventList" : "userLogin");
        }
    }

}