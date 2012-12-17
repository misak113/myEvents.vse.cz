<?php
namespace app\models\authentication;

use My\Db\Table;

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

}

?>