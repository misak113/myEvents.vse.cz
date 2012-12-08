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
	 * @param string $id
	 * @return User|null
	 */
	public function getById($id) {
		return $this->fetchRow(array('user_id = ?' => $id));
	}

}

?>