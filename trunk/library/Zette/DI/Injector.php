<?php
namespace Zette\DI;

use Nette\DI\Container;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 30.11.12
 * Time: 21:43
 * To change this template use File | Settings | File Templates.
 */
class Injector
{
	/** @var \Nette\DI\Container */
	protected $context;

	public function __construct(Container $context) {
		$this->context = $context;
	}

	public function tryInject($instance) {
		// setContext @deprecated
		if (method_exists($instance, 'setContext')) {
			$this->context->callMethod(array($instance, 'setContext'));
		}
		// inject Methods
		foreach (array_reverse(get_class_methods($instance)) as $method) {
			if (substr($method, 0, 6) === 'inject') {
				$this->context->callMethod(array($instance, $method));
			}
		}

	}

	public function trySetTableDependencies($instance) {
		// setTableDependencies
		if (method_exists($instance, 'setTableDependencies')) {
			$this->context->callMethod(array($instance, 'setTableDependencies'));
		}

	}

}
