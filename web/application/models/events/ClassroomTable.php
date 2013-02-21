<?php
namespace app\models\events;

use My\Db\Table;

class ClassroomTable extends Table
{

	/**
	 * Nazev databazove tabulky
	 *
	 * @var string
	 */
	protected $_name = 'classroom';

	/**
	 * Nazev tridy predstavujici jeden zaznam
	 *
	 * @var string
	 */
	protected $_rowClass = 'app\models\events\Classroom';
        
        public function getClassrooms() {
            $query = $this->select()->from($this, array('name', 'capacity'));
            $res = $this->fetchAll($query);
            return $res->toArray();
        }
       
}

?>