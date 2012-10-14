<?php

/**
 * Uvodni inicializace aplikace
 *
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

	/**
	 * @return Nette\DI\Container
	 */
	public function _initContext() {
		//ZetteLoader apod.
		//require_once '../';
		Zend_Loader::loadClass('\Zette\DI\Loader');
		$zetteLoader = new \Zette\DI\Loader();
		$zetteLoader->load();
		$context = $zetteLoader->getContext();
		return $context;
	}
	
	/**
	 * Prida do include path adresar s modely
	 */
	protected function _initIncludePath() {
        $rootDir = dirname(dirname(__FILE__));
        
        set_include_path(get_include_path()            
            . PATH_SEPARATOR . $rootDir. '/application/models'
        );  
	}
	
	/**
	 * Nastaveni DOCTYPE webu
	 *
	 */
	protected function _initDoctype() {

		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->doctype('HTML5');
		
	}
	
	/**
	 * 
	 * Nastaveni helperu
	 * 
	 */
	protected function _initHelpers() {
	    $view = $this->getResource('view');
               
        $prefix = 'My_View_Helper';
        $dir = APPLICATION_PATH . '/../library/My/View/Helper';
        $view->addHelperPath($dir, $prefix);    
	}
	
	
	/**
	 * Nastaveni prepisu URL
	 *
	 * @param array $options
	 */
	protected function _initRequest(array $options = array()) {
		
		$this->bootstrap('FrontController');
		$frontController = $this->getResource('FrontController');
		$router = $frontController->getRouter();
		
		$router->addRoute('aboutUs', new Zend_Controller_Router_Route('o-nas', array(
				'controller' => 'index',
				'action' => 'about'
			))
		);
		$router->addRoute('contact', new Zend_Controller_Router_Route('kontakt', array(
				'controller' => 'index',
				'action' => 'contact'
			))
		);
		$router->addRoute(
			'productList',
			new Zend_Controller_Router_Route('produkty',
				array('controller' => 'product'))
		);
		$router->addRoute(
			'product',
			new Zend_Controller_Router_Route('produkty/:title/:id', array(
				'controller' => 'product',
				'action' => 'preview'
			), array(
				'id' => '\d+'
			))
		);
		$router->addRoute('articleList', new Zend_Controller_Router_Route('clanky',
				array('controller' => 'article'))
		);
		$router->addRoute('article', new Zend_Controller_Router_Route('clanek/:id/:title', array(
				'controller' => 'article',
				'action' => 'detail'
			), array(
				'id' => '\d+'
			))
		);
	}
	
}

?>