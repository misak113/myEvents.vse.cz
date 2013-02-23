<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;
use \app\models\organizations\OrganizationTable;
use Nette\DI\Container;
use app\models\authorization\ResourceTable;
use app\models\authorization\PrivilegeTable;
use app\models\authorization\PermissionTable;
use app\models\authentication\UserTable;
use app\services\CodeParser;

/*
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 17.11.12
 * Time: 16:39
 * To change this template use File | Settings | File Templates.
 */

class Admin_SystemController extends BaseController {

    /** @var TitleLoader */
    protected $titleLoader;

    /** @var OrganizationTable */
    protected $organizationTable;

    /** @var \app\models\organizations\OrganizationHasUserTable */
    protected $organizationHasUserTable;

    /** @var \app\models\authorization\RoleTable */
    protected $roleTable;
    /** @var \app\services\GcmMessanger */
	protected $gcmMessanger;
	/** @var Zend_Controller_Router_Rewrite */
	protected $router;
	/** @var \app\models\authorization\ResourceTable */
	protected $resourceTable;
	/** @var \app\models\authorization\PrivilegeTable */
	protected $privilegeTable;
	/** @var \app\models\authorization\PermissionTable */
	protected $permissionTable;
	/** @var \app\services\CodeParser */
	protected $codeParser;
	/** @var \app\models\authentication\UserTable */
	protected $userTable;


    public function init() {
        $this->_helper->layout->setLayout('admin_sys');
    }

    /**
     * @param TitleLoader $titleLoader 
     */
    public function setContext(TitleLoader $titleLoader) {
        $this->titleLoader = $titleLoader;
    }

    public function indexAction() {
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:index');
    }

    public function adminsAction() {
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:index');
        $this->template->organizations = $this->organizationTable->getOrganizations();

        $role = $this->roleTable->getOrCreateRole($roleName = 'orgAdmin');
        $this->template->users = $role->getUsers();

        $this->template->actionUrl = $this->_helper->url->url();


        if ($this->_request->isPost()) {
            $values = $this->_request->getPost();

            $this->organizationHasUserTable->saveAdmins($values);

			$this->flashMessage('Admini organizací byli uloženi');

            $this->_helper->redirector->gotoRoute(
                    array(
                'module' => 'admin',
                'controller' => 'system',
                'action' => 'admins'
                    ), 'default', true
            );
        } else {
            $orgUsers = $this->organizationHasUserTable->getAdmins();
            if ($orgUsers) {
                $adminArray = array();
                foreach ($orgUsers as $orgUser) {
                    $adminArray[$orgUser->organization_id][] = $orgUser->user_id;
                }
                $this->template->adminArray = $adminArray;
            }
        }
    }

    public function androidAction() {
        $this->template->title = $this->titleLoader->getTitle('Admin:System:android');

        if (isset($_POST["forceAndroidSync"])) {
            $this->gcmMessanger->sendSyncAllMessage(true);
            $this->flashMessage("Příkaz k vynucené synchronizaci byl rozeslán");
        }
    }

    public function forcesyncAction() {
        if ($this->_request->isPost()) {
            $this->gcmMessanger->sendSyncAllMessage(true);
            $this->flashMessage("Příkaz k vynucené synchronizaci byl rozeslán");
        }

        $this->_helper->redirector->gotoRoute(array('module' => 'admin',
            'controller' => 'system',
            'action' => 'android'), 'default', true);
    }

	public function aclmanagerAction() {
		$this->template->title = $this->titleLoader->getTitle('Admin:system:aclmanager');

		if ($permission = $this->getRequest()->getPost('permission')) {
			$status = $this->permissionTable->updatePermissions($permission);
			if ($status) {
				$this->flashMessage('Oprávnění byla uložena');
				$this->redirect('adminAclManager');
			} else {
				$this->flashMessage('Při ukládání oprávnění došlo k chybě', self::FLASH_ERROR);
			}
		}

		$roles = $this->roleTable->fetchAll(null, 'name');
		$resources = $this->resourceTable->fetchAll(null, 'name');
		$privileges = $this->privilegeTable->fetchAll(null, 'name');

		$this->template->roles = $roles;
		$this->template->resources = $resources;
		$this->template->privileges = $privileges;
		$this->template->acceptablePrivileges = $this->codeParser->findAcceptablePrivileges();
	}

	public function usersAction() {
		$this->template->title = $this->titleLoader->getTitle('Admin:system:users');

		if ($role = $this->getRequest()->getPost('role')) {
			$status = $this->userTable->updateRoles($role);
			if ($status) {
				$this->flashMessage('Role byly uloženy');
				$this->redirect('adminUsers');
			} else {
				$this->flashMessage('Při ukládání rolí došlo k chybě', self::FLASH_ERROR);
			}
		}

		$users = $this->userTable->getUsers();
		$roles = $this->roleTable->fetchAll();

		$this->template->users = $users;
		$this->template->roles = $roles;
	}


    public function injectTables(
		OrganizationTable $organizationTable, \app\models\organizations\OrganizationHasUserTable $organizationHasUserTable, app\models\authorization\RoleTable $roleTable, app\services\GcmMessanger $gcmMessanger
		,ResourceTable $resourceTable, PrivilegeTable $privilegeTable, PermissionTable $permissionTable, UserTable $userTable) {
        $this->organizationTable = $organizationTable;
        $this->organizationHasUserTable = $organizationHasUserTable;
        $this->roleTable = $roleTable;
		$this->resourceTable = $resourceTable;
		$this->privilegeTable = $privilegeTable;
        $this->gcmMessanger = $gcmMessanger;
		$this->permissionTable = $permissionTable;
		$this->userTable = $userTable;
    }

	public function injectCodeParser(CodeParser $codeParser) {
		$this->codeParser = $codeParser;
	}

}
