<?php
namespace app\services;

use Nette\Security\User as NetteUser;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 8.12.12
 * Time: 17:44
 * To change this template use File | Settings | File Templates.
 */
class User extends NetteUser
{

	public function __construct(\Nette\Security\IUserStorage $storage, \Nette\DI\Container $context) {
		parent::__construct($storage, $context);
		/** @var \app\models\authorization\RoleTable $roleTable  */
		$roleTable = $context->getService('roleTable');
		$authenticatedRole = $roleTable->getOrCreateRole($this->authenticatedRole);
		$guestRole = $roleTable->getOrCreateRole($this->guestRole);
		$this->authenticatedRole = $authenticatedRole ?$authenticatedRole->getUriCode() :null;
		$this->guestRole = $guestRole ?$guestRole->getUriCode() :null;
	}

}
