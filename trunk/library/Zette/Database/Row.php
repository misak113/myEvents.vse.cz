<?php
namespace Zette\Database;

use Zette\Caching\ICacheable;
use Zette\DI\Injector;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 6.1.13
 * Time: 21:02
 * To change this template use File | Settings | File Templates.
 */
class Row extends \Zend_Db_Table_Row_Abstract implements ICacheable
{
	/** @var Injector */
	protected $injector;

	public function __construct(array $config = array()) {
		parent::__construct($config);
		// @todo this is shit :/
		$context = \Nette\Environment::getContext();
		$context->callMethod(array($this, 'injectInjector'));
		$this->injector->tryInject($this);
	}

	public function injectInjector(Injector $injector) {
		$this->injector = $injector;
	}

	public function cache() {
		$value = $this->getTable()->getClassCache()->getCachedInstance($this);
		return $value;
	}
}
