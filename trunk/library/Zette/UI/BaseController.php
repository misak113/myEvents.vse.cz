<?phpnamespace Zette\UI;use Nette\DI\Container;use Zend_Controller_Action;use Zend_Controller_Request_Abstract;use Zend_Controller_Response_Abstract;use Zette\DI\Injector;use Zette\Services\Constants\FlashConstants;/** * @author: Misak113 * @date-created: 2.10.12 */class BaseController extends Zend_Controller_Action implements FlashConstants {	/** @var Container */	protected $context;	/** @var \Bootstrap */	protected $bootstrap;	protected $persistentParams = false;	/** @var \Nette\Templating\FileTemplate */	public $template;	/** @deprecated */	public $view;	/** @var bool */	private $startupCheck = false;	public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {		parent::__construct($request, $response, $invokeArgs);		$this->bootstrap = $invokeArgs['bootstrap'];		$this->context = $this->bootstrap->getContainer()->context;		$this->template = $this->view;		$this->view->setPresenter($this);		$this->afterConstruct(); // @todo dát až po konstruktoru (někde preDispatch)	}	private function afterConstruct() {		$injector = $this->context->getService('zette.injector');		$injector->tryInject($this);		$this->startup();		if (!$this->startupCheck) {			throw new \Nette\InvalidStateException("Method Contrller::startup() or its descendant doesn't call parent::startup().");		}	}	protected function getComponent($name) {		return $this->view->getComponent($name);	}	final public function preDispatch() {		$this->beforeRender();	}	final public function postDispatch() {		$this->afterRender();		$this->template->errorFlashes = $this->_helper->flashMessenger->getMessages(self::FLASH_ERROR);		$this->template->infoFlashes = $this->_helper->flashMessenger->getMessages(self::FLASH_INFO);		$this->template->flashes = array_merge($this->template->errorFlashes, $this->template->infoFlashes);	}	/**	 * Přeloží text do aktuálně nastaveného jazyka	 * @param string $text	 * @param array $params	 * @return string	 */	protected function t($text, $params = array()) {		// @todo Translator		return vsprintf($text, $params);	}	/**	 * @deprecated	 **/	protected function url() {		return call_user_func_array(array($this->view, 'url'), func_get_args());	}	/**	 * @param string $destination	 * @param array|null $args	 */	public function link($destination, $args = array()) {		$blocks = explode(':', $destination);		$action = end($blocks);		$controller = prev($blocks);		$module = prev($blocks);		$params = array();		if ($controller === false) {			if ($action === 'this') {				// this znamená aktuální				$action = '';				$controller = '';				$module = '';			} else {				// Když je pouze string je to routeName				$routeName = $action;				$params = $args;				$url = $this->url($params, $routeName, !$this->persistentParams);				return $url;			}		}		if ($action === '') {			$action = $this->getRequest()->getActionName();		}		$params['action'] = strtolower($action);		if ($controller === '') {			// Když naní zadán controller (:actinName) je vyplněn aktuální			$controller = $this->getRequest()->getControllerName();		}		$params['controller'] = strtolower($controller);		if ($module === false) {			// Když vubec nevyplní, je Default (presenter:action)			$module = 'default';		}		if ($module === '') {			// Když je prázdný (:presenter:action) tak nachá aktuální			$module = $this->getRequest()->getModuleName();		}		$params['module'] = strtolower($module);		$params += $args;		$url = $this->url($params, null, !$this->persistentParams);		return $url;	}	protected function  setLayout($name) {		$this->_helper->layout->setLayout($name);	}	protected function flashMessage($message, $type = self::FLASH_INFO) {		$this->_helper->flashMessenger->addMessage($message, $type);	}	/** Overiden methods */	protected function beforeRender() { }	protected function afterRender() { }	protected function startup() {		$this->startupCheck = TRUE;	}}