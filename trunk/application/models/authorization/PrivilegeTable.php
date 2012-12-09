<?php
namespace app\models\authorization;

use My\Db\Table;

class PrivilegeTable extends Table
{

	/**
	 * Nazev databazove tabulky
	 *
	 * @var string
	 */
	protected $_name = 'privilege';

	/**
	 * Nazev tridy predstavujici jeden zaznam
	 *
	 * @var string
	 */
	protected $_rowClass = 'app\models\authorization\Privilege';

	/**
	 * @param string $name
	 * @return Privilege
	 */
	public function getOrCreatePrivilege($name) {
		$privilege = $this->fetchRow(array('uri_code = ?' => $name));
		if (!$privilege) {
			$data = array(
				'name' => $name,
				'uri_code' => $name,
			);
			$privilege = $this->createRow($data);
		}
		$privilege->save();

		return $privilege;
	}

}

?>