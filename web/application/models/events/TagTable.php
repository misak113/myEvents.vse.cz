<?php
namespace app\models\events;

use My\Db\Table;

class TagTable extends Table
{
	const TYPE_TAGS = 1;
	const TYPE_PLACES = 2;

	/**
	 * Nazev databazove tabulky
	 *
	 * @var string
	 */
	protected $_name = 'tag';

	/**
	 * Nazev tridy predstavujici jeden zaznam
	 *
	 * @var string
	 */
	protected $_rowClass = 'app\models\events\Tag';

	/** @return Zend_Db_Table_Rowset */
	public function getTags() {
		$where = array('tag_type_id = ?' => self::TYPE_TAGS);
		$res = $this->fetchAll($where, 'name');
	
		return $res;
	}
	/** @return Zend_Db_Table_Rowset */
	public function getPlaces() {
		$where = array('tag_type_id = ?' => self::TYPE_PLACES);
		$res = $this->fetchAll($where, 'name');

		return $res;
	}
}

?>