<?php

use Zette\Services\PluginController;
use app\models\authentication\UserTable;
use Nette\Security\IAuthorizator;
use Nette\Security\Permission;
use app\models\authorization\RoleTable;
use app\models\authorization\ResourceTable;

/**
 * Plugin resi autorizaci uzivatele
 * Pokud uzivatel nema pristup do vybraneho controlleru/akce
 * je presmerovan na prihlasovaci obrazovku
 * do messengeru je pridana chybova hlaska
 */
class Application_Plugin_Acl extends PluginController {

	/** @var UserTable @inject */
	protected $userTable;
	/** @var \Nette\Security\Permission @inject */
	protected $authorizator;
	/** @var \app\models\authorization\RoleTable @inject */
	protected $roleTable;
	/** @var \app\models\authorization\ResourceTable @inject */
	protected $resourceTable;

	public function injectUserTable(UserTable $userTable) {
		$this->userTable = $userTable;
	}
	public function injectAuthorizator(IAuthorizator $authorizator) {
		$this->authorizator = $authorizator;
	}
	public function injectRoleTable(RoleTable $roleTable) {
		$this->roleTable = $roleTable;
	}
	public function injectResourceTable(ResourceTable $resourceTable) {
		$this->resourceTable = $resourceTable;
	}

    /**
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
   		$options = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getApplication()->getOptions();

		$this->initPermissions();

		$module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

		if ($module == 'default') return; // Frontend vždy povolen

        $resource = $controller;
        $privilege = $action ?$action :'index';

		try {
			$isAllowed = $this->user->isAllowed($resource, $privilege);

			// Přístup nepovolen
			if (!$isAllowed) {
				$this->flashMessage('Nejste přihlášen nebo nemáte patřičné oprávnění', self::FLASH_ERROR);
				$this->redirect('userLogin');
			}
		} catch (\Nette\InvalidStateException $e) {
			$this->flashMessage('Požadované oprávnění "'.$resource.':'.$privilege.'" neexistuje', self::FLASH_ERROR);
			$this->redirect('eventList');
		}
    }


	protected function initPermissions() {

		$resources = $this->resourceTable->fetchAll();
		foreach ($resources as $resource) {
			$this->authorizator->addResource($resource->getUriCode());
		}

		$roles = $this->roleTable->fetchAll();
		foreach ($roles as $role) {
			/** @var \app\models\authorization\Role $role */
			$this->authorizator->addRole($role->getUriCode());
			$resources = array();
			foreach ($role->getResources() as $resource) {
				$resources[] = $resource->getUriCode();
			}
			$privileges = array();
			foreach ($role->getPrivileges() as $privilege) {
				$privileges[] = $privilege->getUriCode();
			}
			$this->authorizator->allow($role->getUriCode(), $resources, $privileges);
		}

		// sysAdmin má vše :)
		$this->authorizator->allow('sysAdmin', Permission::ALL, Permission::ALL);


	}

}