<?php
namespace Zette\Services;

use Zend_Controller_Plugin_Abstract;
use Zette\Services\Constants\FlashConstants;
use Zend_Controller_Action_HelperBroker;
use Zend_Controller_Action_Helper_Redirector;
use Zend_Controller_Action_Helper_FlashMessenger;
use Nette\DI\Container;
use Zend_Controller_Front;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 5.12.12
 * Time: 18:26
 * To change this template use File | Settings | File Templates.
 */
class PluginController extends Zend_Controller_Plugin_Abstract implements FlashConstants
{

	/** @var bool */
	private $startupCheck = false;

	/** @var Zend_Controller_Action_Helper_Redirector */
	protected $redirector;
	/** @var Zend_Controller_Action_Helper_FlashMessenger */
	protected $flashMessenger;
	/** @var \Zend_Db_Adapter_Abstract */
	protected $connection;
	/** @var bool */
	protected $persistentParams = false;
	/** @var \Nette\DI\Container */
	protected $context;
	/** @var \Nette\Security\User */
	protected $user;


	public function routeStartup(\Zend_Controller_Request_Abstract $request) {
		parent::routeStartup($request);
		$this->afterConstruct(); // @todo dát až po konstruktoru (někde preDispatch)
	}

	private function afterConstruct() {
		$this->context = Zend_Controller_Front::getInstance()->getParam('bootstrap')->getContainer()->context;
		$this->redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');
		$this->flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
		$this->connection = \My\Db\Table::getDefaultAdapter(); // @todo
		$this->user = $this->context->getService('user');// Vyžádáme si identitu přihlášeného uživatele

		/** @var \Zette\DI\Injector $injector  */
		$injector = $this->context->getService('zette.injector');
		$injector->tryInject($this);

		$this->startup();
		if (!$this->startupCheck) {
			throw new \Nette\InvalidStateException("Method Contrller::startup() or its descendant doesn't call parent::startup().");
		}
	}

	/**
	 * Vyhodí flashMessage daného typu
	 * @param string $message Zpráva pro vypsání
	 * @param string $type typ zprávy
	 */
	protected function flashMessage($message, $type = self::FLASH_INFO) {
		$this->flashMessenger->addMessage($message, $type);
	}

	/**
	 * Přesměruje na zadanou routy s zadanými parametry
	 * @param string $route routa (zatím route, později i Module:Presenter:action)
	 * @param array $params dodatečné parametry
	 */
	protected function redirect($route, $params = array()) {
		$this->redirector->gotoRouteAndExit($params, $route, !$this->persistentParams);
	}

	protected function startup() {
		$this->startupCheck = TRUE;
	}
}
