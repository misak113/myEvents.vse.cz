<?php
namespace app\models\authentication;

use My\Db\Table;
use app\models\authorization\UserHasRoleTable;

class UserTable extends Table
{

	/**
	 * Nazev databazove tabulky
	 *
	 * @var string
	 */
	protected $_name = 'user';

	/**
	 * Nazev tridy predstavujici jeden zaznam
	 *
	 * @var string
	 */
	protected $_rowClass = 'app\models\authentication\User';

	/** @var \app\models\authorization\UserHasRoleTable */
	protected $userHasRoleTable;

	/**
	 * @param int $id
	 * @return User|null
	 */
	public function getById($id) {
		return $this->fetchRow(array('user_id = ?' => $id));
	}
	/**
	 * @param string $email
	 * @return User|null
	 */
	public function getByEmail($email) {
		return $this->fetchRow(array('email = ?' => $email));
	}


	/**
	 * @return \Zend_Db_Table_Rowset_Abstract
	 */
	public function getUsers() {
		$users = $this->fetchAll(null, 'last_login_date DESC');

		return $users;
	}

	public function updateRoles(array $users) {
		// radši v transakci
		$this->_db->beginTransaction();
		// Smazat všechno
		$this->userHasRoleTable->delete(array());
		// přidat postupně
		foreach ($users as $user_id => $roles) {
			foreach ($roles as $role_id => $value) {
				if ($value) {
					$userHasRole = $this->userHasRoleTable->createRow(array(
						'role_id' => $role_id,
						'user_id' => $user_id,
					));
					$userHasRole->save();
				}
			}
		}
		// commitnutí transakce
		$status = $this->_db->commit();
		return (bool)$status;
	}

	public function injectUserHasRoleTable(UserHasRoleTable $userHasRoleTable) {
		$this->userHasRoleTable = $userHasRoleTable;
	}

}

?>