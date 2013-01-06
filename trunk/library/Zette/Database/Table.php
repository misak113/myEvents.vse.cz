<?php
namespace Zette\Database;

use Zette\Caching\ClassCache;
use Zette\Caching\ICacheable;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 6.1.13
 * Time: 18:15
 * To change this template use File | Settings | File Templates.
 */
class Table extends \Zend_Db_Table_Abstract implements ICacheable
{
	/** @var ClassCache @inject */
	protected $classCache;

	public function injectCacheClass(ClassCache $classCache) {
		$this->classCache = $classCache;
	}

	/**
	 * @return \Zette\Caching\ClassCache
	 */
	public function getClassCache() {
		return $this->classCache;
	}

	public function cache() {
		$value = $this->classCache->getCachedInstance($this);
		return $value;
	}

}
