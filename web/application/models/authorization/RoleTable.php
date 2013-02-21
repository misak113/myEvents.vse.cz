<?php
namespace app\models\authorization;

use My\Db\Table;

class RoleTable extends Table
{

	/**
	 * Nazev databazove tabulky
	 *
	 * @var string
	 */
	protected $_name = 'role';

	/**
	 * Nazev tridy predstavujici jeden zaznam
	 *
	 * @var string
	 */
	protected $_rowClass = 'app\models\authorization\Role';


	/**
	 * Vrátí roli podle uri_code a pokud neexistuje tak ji vytvoří
	 * @param string $role uri_code dané role
	 * @return Role
	 */
	public function getOrCreateRole($roleUriCode) {
		$role = $this->fetchRow(array('uri_code = ?' => $roleUriCode));
		if (!$role) {
			$roleData = array(
				'name' => $roleUriCode,
				'uri_code' => $roleUriCode,
				'level' => 0,
			);
			$role = $this->createRow($roleData);
		}
		$role->save();

		return $role;
	}
        
      
}

?>