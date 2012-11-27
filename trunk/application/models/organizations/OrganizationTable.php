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


}

?>