<?php
namespace app\models\authentication;

use My\Db\Table;

class GcmRegistrationTable extends Table
{

	/**
	 * Nazev databazove tabulky
	 *
	 * @var string
	 */
	protected $_name = 'gcm_registration';

	/**
	 * Nazev tridy predstavujici jeden zaznam
	 *
	 * @var string
	 */
	protected $_rowClass = 'app\models\authentication\GcmRegistration';
        

	/**
	 * @param int $id
	 * @return GcmRegistration|null
	 */
	public function getById($id) {
		return $this->fetchRow(array('gcm_registration_id = ?' => $id));
	}
        

	/**
	 * @param string $regId
	 * @return User|null
	 */
	public function getByRegId($regId) {
		return $this->fetchRow(array('reg_id = ?' => $regId));
	}

}

?>