<?php
namespace Zette\Templating;

use Nette\Object;
use Nette\DI\Container;
use Zend_Filter_Inflector;
use Zend_Layout;
use Zend_Registry;
use Zend_Controller_Action_HelperBroker;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 18.11.12
 * Time: 20:11
 * To change this template use File | Settings | File Templates.
 */
class Loader extends Object
{
	/** @var \Nette\DI\Container */
	protected $context;

	public function __construct(Container $context) {
		$this->context = $context;
	}

	/**
	 * @return LatteView
	 */
	public function createView() {
		$view = new \Zette\Templating\LatteView();
		if (method_exists($view, 'setContext')) {
			$this->context->callMethod(array($view, 'setContext'));
		}

		Zend_Registry::set('view', $view);

		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
		$viewRenderer->setView($view)
				->setViewScriptPathSpec(':controller/:action.:suffix')
				->setViewSuffix('latte');

		$inflector = new Zend_Filter_Inflector(':script.:suffix');
		$inflector->addRules(
			array(
				'script' => 'layout',
				'suffix' => 'latte'
			)
		);

		Zend_Layout::startMvc(
			array(
				'view' => $view,
				'inflector' => $inflector
			)
		);

		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

		return $view;
	}

}
