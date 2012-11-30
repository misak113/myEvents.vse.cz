<?php
namespace app\models\organizations;

use My\Db\Table;

class OrganizationTable extends Table
{

	/**
	 * Nazev databazove tabulky
	 *
	 * @var string
	 */
	protected $_name = 'organization';

	/**
	 * Nazev tridy predstavujici jeden zaznam
	 *
	 * @var string
	 */
	protected $_rowClass = 'app\models\organizations\Organization';

	/**
	 * @return array
	 */
	public function getOrganizations() {
		$where = array();
		$res = $this->fetchAll($where, 'name'); // @todo seřadit podle oblíbenosti ORG. a podle preferencí uživatele

		return $res->toArray();
	}

}

?>