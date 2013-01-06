<?php

use Zette\Services\PluginController;
use app\models\authentication\UserTable;
use Nette\Security\IAuthorizator;
use Nette\Security\Permission;
use app\models\authorization\RoleTable;
use app\models\authorization\ResourceTable;
use app\models\authorization\PrivilegeTable;
use Nette\Reflection\ClassType;

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
	/** @var \app\models\authorization\PrivilegeTable */
	protected $privilegeTable;

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
	public function injectPrivilegeTable(PrivilegeTable $privilegeTable) {
		$this->privilegeTable = $privilegeTable;
	}

    /**
     *
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
   		$options = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getApplication()->getOptions();

		$this->initPermissions();

		$module = $request->getModuleName();
        $controller = strtolower(str_replace('-', '', $request->getControllerName()));
        $action = strtolower(str_replace('-', '', $request->getActionName()));

		if ($module == 'default') return; // @todo Frontend vždy povolen

        $resource = ($module ?$module.'.' :'').($controller ?$controller :'index');
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


	/**
	 * @todo zacachovat :)
	 */
	protected function initPermissions() {

		// Vytvoření rolí v DB
		$authenticatedRole = $this->roleTable->getOrCreateRole($this->user->authenticatedRole);
		$guestRole = $this->roleTable->getOrCreateRole($this->user->guestRole);
		$sysAdminRole = $this->roleTable->getOrCreateRole('sysAdmin');
		$this->user->authenticatedRole = $authenticatedRole ?$authenticatedRole->getUriCode() :null;
		$this->user->guestRole = $guestRole ?$guestRole->getUriCode() :null;


		// Vytvoření resources a privileges
		$resources = $this->findResources();
		foreach ($resources as $resource) {
			$this->resourceTable->getOrCreateResource($resource);
		}
		$privileges = $this->findPrivileges();
		foreach ($privileges as $privilege) {
			$this->privilegeTable->getOrCreatePrivilege($privilege);
		}




		// nacpán do Permission objektu resources
		$resources = $this->resourceTable->fetchAll();
		foreach ($resources as $resource) {
			$this->authorizator->addResource($resource->getUriCode());
		}


		// Naspány role a k nim přidány permisions
		$roles = $this->roleTable->fetchAll();
		foreach ($roles as $role) {
			/** @var \app\models\authorization\Role $role */
			$this->authorizator->addRole($role->getUriCode());

			// přidá oprávnění
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



		// @todo sysAdmin má vše :)
		$this->authorizator->allow($sysAdminRole->getUriCode(), Permission::ALL, Permission::ALL);


	}


	protected function findResources() {
		$resources = array();

		$modulesDir = APP_DIR.'/modules';
		$modules = scandir($modulesDir);
		foreach ($modules as $module) {
			$controllersDir = $modulesDir.'/'.$module.'/controllers';
			if (!is_dir($controllersDir)) continue;
			$controllers = scandir($controllersDir);
			foreach ($controllers as $controller) {
				if (strstr($controller, 'Controller.php') === false) continue;
				$ctrlName = strtolower(str_replace('Controller.php', '', $controller));
				$resource = ($module != 'default' ?$module.'.' :'').$ctrlName;
				$resources[] = $resource;
			}
		}

		return $resources;
	}

	protected function findPrivileges() {
		$privileges = array();

		$resources = $this->findResources();
		foreach ($resources as $resource) {
			$ex = explode('.', $resource);
			$class = '';
			if (isset($ex[1])) {
				$class = ucfirst($ex[0]).'_';
				$ex[0] = $ex[1];
			}
			$class = $class.ucfirst($ex[0]).'Controller';
			$refClass = new ClassType($class);
			$methods = $refClass->getMethods(ReflectionMethod::IS_PUBLIC);
			foreach ($methods as $method) {
				if (preg_match('~^(.+)Action$~', $method->name, $m)) {
					$privileges[] = strtolower($m[1]);
				}
			}
		}

		return $privileges;
	}

}