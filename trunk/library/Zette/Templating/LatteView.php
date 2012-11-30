<?php
namespace Zette\Templating;

use Nette\DI\Container;
use Nette\Templating\FileTemplate;
use Nette\Caching\Storages\PhpFileStorage;
use Nette\Latte\Engine;
use Zette\UI\ComponentDispatcher;
use Zend_Controller_Action;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 17.11.12
 * Time: 17:16
 * To change this template use File | Settings | File Templates.
 */
class LatteView extends \Zend_View
{

	/** @var string */
	protected $_file;

	/** @var \Nette\DI\Container */
	protected $context;
	/** @var \Nette\Caching\Storages\FileStorage */
	protected $cacheStorage;
	/** @var Engine */
	protected $latteEngine;
	/** @var ComponentDispatcher */
	protected $componentDispatcher;
	/** @var Zend_Controller_Action */
	protected $presenter;

	public function __construct(array $config = array()) {
		parent::__construct($config);
	}

	public function setContext(Container $context, ComponentDispatcher $componentDispatcher) {
		$this->context = $context;
		$this->componentDispatcher = $componentDispatcher;
		$this->cacheStorage = $context->nette->templateCacheStorage;
		$this->latteEngine = $context->nette->createLatte();
	}

	public function render($name) {
		//return parent::render($name);

		// find the script file name using the parent private method
		$this->_file = $this->_script($name);

		$template = $this->createTemplate();
		$template->setFile($this->_file);

		// Naplnění template proměnných
		foreach ($this->getVars() as $var => $value) {
			$template->$var = $value;
		}
		$template->view = $this;

		ob_start();

		$template->render();

		return ob_get_clean(); // filter output
	}

	/**
	 * @see Control in Nette
	 * @param  string|NULL
	 * @return \Nette\Templating\ITemplate
	 */

	public function createTemplate($class = NULL)

	{

		$template = $class ? new $class : new FileTemplate;


		$this->templatePrepareFilters($template);

		$template->registerHelperLoader('Nette\Templating\Helpers::loader');

		$template->setCacheStorage($this->cacheStorage);


		// default parameters

		$presenter = $this->presenter = \Zette\UI\BaseController::getActual();

		$this->componentDispatcher->setPresenter($presenter);

		$template->control = $template->_control = $this;

		$template->presenter = $template->_presenter = $presenter;

		if ($presenter instanceof Presenter) {



			$template->user = $presenter->getUser();

			$template->netteHttpResponse = $presenter->getHttpResponse();

			$template->netteCacheStorage = $presenter->getContext()->getByType('Nette\Caching\IStorage');

			$template->baseUri = $template->baseUrl = rtrim($presenter->getHttpRequest()->getUrl()->getBaseUrl(), '/');

			$template->basePath = preg_replace('#https?://[^/]+#A', '', $template->baseUrl);



			// flash message

			if ($presenter->hasFlashSession()) {

				$id = $this->getParameterId('flash');

				$template->flashes = $presenter->getFlashSession()->$id;

			}

		}

		if (!isset($template->flashes) || !is_array($template->flashes)) {

			$template->flashes = array();

		}


		/**
		 * Descendant can override this method to customize template compile-time filters.
		 * @param  Nette\Templating\Template
		 * @return void
		 */





		return $template;

	}

	public function templatePrepareFilters($template) {
		$template->registerFilter($this->latteEngine);
	}


	public function getComponent($name) {
		return $this->componentDispatcher->getComponent($name);
	}

}
