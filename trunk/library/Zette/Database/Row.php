<?php
namespace Zette\Database;

use Zette\Caching\ICacheable;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 6.1.13
 * Time: 21:02
 * To change this template use File | Settings | File Templates.
 */
class Row extends \Zend_Db_Table_Row_Abstract implements ICacheable
{

	public function cache() {
		$value = $this->getTable()->getClassCache()->getCachedInstance($this);
		return $value;
	}
}
