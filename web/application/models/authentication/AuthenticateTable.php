<?php
namespace app\models\authentication;

use My\Db\Table;

class AuthenticateTable extends Table
{

	/**
	 * Nazev databazove tabulky
	 *
	 * @var string
	 */
	protected $_name = 'authenticate';

	/**
	 * Nazev tridy predstavujici jeden zaznam
	 *
	 * @var string
	 */
	protected $_rowClass = 'app\models\authentication\Authenticate';


	/**
	 * Reference
	 *
	 * @var array
	 */
	protected $_referenceMap = array (
			'User' => array(
					'columns' => array ('user_id'),
					'refTableClass' => 'app\models\authentication\UserTable',
					'refColumns' => array ('user_id')
			),
			'AuthenticateProvides' => array(
					'columns' => array ('user_id'),
					'refTableClass' => 'app\models\authentication\AuthenticateProvidesTable',
					'refColumns' => array ('user_id')
			),
	);

	/**
	 * @param string $identity
	 * @param int $authenticateProvidesId
	 * @return Authenticate
	 */
	public function getByIdentity($identity, $authenticateProvidesId) {
		return $this->fetchRow(array(
			'identity = ?' => $identity,
			'authenticate_provides_id = ?' => $authenticateProvidesId,
		));
	}
}

?>