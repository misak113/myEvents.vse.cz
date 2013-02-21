<?php
namespace app\models\events;

use My\Db\Table;

class CategoryTable extends Table
{

	/**
	 * Nazev databazove tabulky
	 *
	 * @var string
	 */
	protected $_name = 'category';

	/**
	 * Nazev tridy predstavujici jeden zaznam
	 *
	 * @var string
	 */
	protected $_rowClass = 'app\models\events\Category';

	/**
	 * @return array
	 */
	public function getCategories() {
		$where = array();
		$res = $this->fetchAll($where, 'name');

		return $res->toArray();
	}

}

?>