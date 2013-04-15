<?php
namespace Zette\Config;

use Zette\Diagnostics\TimerPanel;
/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 6.12.12
 * Time: 23:41
 * To change this template use File | Settings | File Templates.
 */
class ZetteBootstrap extends \Zend_Application_Bootstrap_Bootstrap
{

	/** @var \Zend_Controller_Router_Rewrite */
	protected $router;

	/** @var \Nette\DI\Container */
	protected $context;

	public function setResources($resources) {
		$this->loadZette();
		foreach ($resources as $resource => $resourceOptions) {
			if ($resource == 'db') {
				$resourceOptions = $this->configDatabase($resourceOptions);
			}
			$this->registerPluginResource($resource, $resourceOptions);
		}
	}

	protected function loadZette() {
		// ZetteLoader
		\Zend_Loader::loadClass('\Zette\DI\Loader');

		TimerPanel::start('loading Zette');
		$zetteLoader = new \Zette\DI\Loader();
		$zetteLoader->load();
		$this->context = $zetteLoader->getContext();

		TimerPanel::stop('loading Zette');
		TimerPanel::traceTime('Zette loaded');
	}
	protected function configDatabase($options) {
		$parameters = $this->context->getParameters();
		$options['params']['host'] = $parameters['database']['host'];
		$options['params']['username'] = $parameters['database']['username'];
		$options['params']['password'] = $parameters['database']['password'];
		$options['params']['dbname'] = $parameters['database']['dbname'];
		return $options;
	}


	/**
	 * @return \Nette\DI\Container
	 */
	protected function _initContext() {

		//#### Set Db profiler to Nette Debuger @todo to Zette library
		/** @var \Zend_Application_Resource_Db $db  */
		$db = $this->getPluginResource('db');
		/** @var \Zette_Database_Pdo_Mysql $adapter  */
		$adapter = $db->getDbAdapter();
		$connectionPanel = $this->context->getService('zette.connectionPanel');
		$adapter->setConnectionPanel($connectionPanel);
		/** @var \Zette\Database\Diagnostics\ConnectionPanel $connectionPanel  */
		$connectionPanel->setProfiler($adapter->getProfiler());



		TimerPanel::traceTime();
		return $this->context;
	}

	protected function _initSession() {
		// @todo to Zette
		try {
			$this->context->getService('session')->start();
			\Zend_Session::$_unitTestEnabled = true;
			\Zend_Session::start();
			\Zend_Session::$_unitTestEnabled = false;
		} catch (\Exception $e) {}
		TimerPanel::traceTime();
	}

	protected function _initView()
	{
		// LatteView
		$context = $view = $this->getResource('context');
		$viewLoader = new \Zette\Templating\Loader($context);
		$view = $viewLoader->createView();

		TimerPanel::traceTime();
		return $view;
	}

	protected function _initRouter() {
		$this->bootstrap('FrontController');
		/** @var Zend_Controller_Front $frontController  */
		$frontController = $this->getResource('FrontController');
		$frontController->throwExceptions(true); // @todo to Zette loader
		$this->router = $frontController->getRouter();

	}


	/**
	 * Get class resources (as resource/method pairs)
	 *
	 * Uses get_class_methods() by default, reflection on prior to 5.2.6,
	 * as a bug prevents the usage of get_class_methods() there.
	 *
	 * @return array
	 */
	public function getClassResources()
	{
		if (null === $this->_classResources) {
			$firstMethods = array('_initContext', '_initSession', '_initView', '_initRouter');
			$allMethods = get_class_methods($this);
			$allMethods = array_intersect($allMethods, array_diff($allMethods, $firstMethods));
			$methodNames = array_merge($firstMethods, $allMethods);

			$this->_classResources = array();
			foreach ($methodNames as $method) {
				if (5 < strlen($method) && '_init' === substr($method, 0, 5)) {
					$this->_classResources[strtolower(substr($method, 5))] = $method;
				}
			}
		}

		return $this->_classResources;
	}
}
