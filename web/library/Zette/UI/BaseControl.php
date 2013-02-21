<?php
namespace Zette\UI;

use Nette\Application\UI\Control;
use Zette\Templating\LatteView;
/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 30.11.12
 * Time: 21:48
 * To change this template use File | Settings | File Templates.
 *
 * @method \Zend_Controller_Request_Abstract getRequest
 */
class BaseControl extends Control
{
	/** @var \Zette\Templating\LatteView */
	protected $view;

	public function setTemplateFactory(LatteView $view) {
		$this->view = $view;
	}

	public function createTemplate($class = null) {
		$template = $this->view->createTemplate($class);
		$template->view = $this->view;
		return  $template;
	}



	/********************* Magic methods ******************/

	public function __call($name, $args) {
		return $this->view->__call($name, $args);
	}

}
