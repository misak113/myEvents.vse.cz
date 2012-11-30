<?php
namespace Zette\UI;

use Zend_Controller_Action;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 30.11.12
 * Time: 23:06
 * To change this template use File | Settings | File Templates.
 */
class ComponentDispatcher
{
	/** @var array */
	protected $components = array();
	/** @var \Nette\Application\UI\Control|Zend_Controller_Action */
	protected $control;

	/**
	 * @param Nette\Application\UI\Control|Zend_Controller_Action $control
	 */
	public function setPresenter($control) {
		$this->control = $control;
	}

	public function getComponent($name) {
		if (isset($this->components[$name])) {
			return $this->components[$name];
		}

		$methodName = 'createComponent'.ucfirst($name);
		if (!method_exists($this->control, $methodName)) {
			throw new \Nette\Application\ApplicationException('Nelze načíst komponentu "'.$name.'" protože chybí továrnička v "'.get_class($this->control).'::'.$methodName.'" ');
		}

		$component = call_user_func(array($this->control, $methodName));
		$component->setTemplateFactory($this->control->view);

		$this->components[$name] = $component;

		return $component;
	}

}
