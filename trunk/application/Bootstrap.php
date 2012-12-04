<?php/** * Uvodni inicializace aplikace * */class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {	/** @var \Nette\DI\Container */	protected $context;	public function setResources($resources) {		$this->loadZette();		foreach ($resources as $resource => $resourceOptions) {			if ($resource == 'db') {				$resourceOptions = $this->configDatabase($resourceOptions);			}			$this->registerPluginResource($resource, $resourceOptions);		}	}	protected function loadZette() {		// ZetteLoader		Zend_Loader::loadClass('\Zette\DI\Loader');		$zetteLoader = new \Zette\DI\Loader();		$zetteLoader->load();		$this->context = $zetteLoader->getContext();	}	protected function configDatabase($options) {		$parameters = $this->context->getParameters();		$options['params']['host'] = $parameters['database']['host'];		$options['params']['username'] = $parameters['database']['username'];		$options['params']['password'] = $parameters['database']['password'];		$options['params']['dbname'] = $parameters['database']['dbname'];		return $options;	}	/**	 * @return Nette\DI\Container	 */	protected function _initContext() {		//#### Set Db profiler to Nette Debuger @todo to Zette library		/** @var \Zend_Application_Resource_Db $db  */		$db = $this->getPluginResource('db');		/** @var \Zette_Database_Pdo_Mysql $adapter  */		$adapter = $db->getDbAdapter();		$connectionPanel = $this->context->getService('zette.connectionPanel');		$adapter->setConnectionPanel($connectionPanel);		/** @var \Zette\Database\Diagnostics\ConnectionPanel $connectionPanel  */		$connectionPanel->setProfiler($adapter->getProfiler());		return $this->context;	}	protected function _initView()	{		// LatteView		$context = $view = $this->getResource('context');		$viewLoader = new \Zette\Templating\Loader($context);		$view = $viewLoader->createView();		return $view;	}	/**	 * Nastaveni helperu	 * @deprecated	 */	protected function _initHelpers() {		$view = $this->getResource('view');		$prefix = 'My_View_Helper';		$dir = APPLICATION_PATH . '/../library/My/View/Helper';		$view->addHelperPath($dir, $prefix);	}	/**	 * Nastaveni prepisu URL	 *	 * @param array $options	 */	protected function _initRequest(array $options = array()) {		$this->bootstrap('FrontController');		$frontController = $this->getResource('FrontController');		$router = $frontController->getRouter();		// Statics		$router->addRoute('aboutUs', new Zend_Controller_Router_Route('o-nas', array(				'module' => 'default',				'controller' => 'index',				'action' => 'about',			))		);		$router->addRoute('contact', new Zend_Controller_Router_Route('kontakt', array(				'module' => 'default',				'controller' => 'index',				'action' => 'contact',			))		);		// Events		$router->addRoute('eventList', new Zend_Controller_Router_Route('udalosti/:categoryId', array(				'module' => 'default',				'controller' => 'event',				'action' => 'list',				'categoryId' => '',			))		);		$router->addRoute('event', new Zend_Controller_Router_Route('udalost/:id/:title', array(				'module' => 'default',				'controller' => 'event',				'action' => 'detail',			), array(				'id' => '\d+',			))		);                                // Admin		$router->addRoute('adminIndex', new Zend_Controller_Router_Route('administrace', array(				'module' => 'admin',				'controller' => 'index',				'action' => 'index',			))		);		$router->addRoute('adminLogin', new Zend_Controller_Router_Route('administrace/login', array(				'module' => 'admin',				'controller' => 'index',				'action' => 'login',			))		);		$router->addRoute('adminLogout', new Zend_Controller_Router_Route('administrace/logout', array(				'module' => 'admin',				'controller' => 'index',				'action' => 'logout',			))		);		$router->addRoute('newEvent', new Zend_Controller_Router_Route('administrace/nova-udalost', array(				'module' => 'admin',				'controller' => 'index',				'action' => 'edit',			))		);		$router->addRoute('editEvent', new Zend_Controller_Router_Route('administrace/editovat-udalost/:id', array(				'module' => 'admin',				'controller' => 'index',				'action' => 'edit',			), array(                            'id' => '\d+',                        ))		);		// Landing page @todo smazat pro real pages		$router->addRoute('landing', new Zend_Controller_Router_Route('', array(				'module' => 'landing',				'controller' => 'index',				'action' => 'index',			))		);	}}