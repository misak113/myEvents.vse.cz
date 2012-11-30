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
 */
class BaseControl extends Control
{
	/** @var \Zette\Templating\LatteView */
	protected $templateFactory;

	public function setTemplateFactory(LatteView $templateFactory) {
		$this->templateFactory = $templateFactory;
	}

	public function createTemplate($class = null) {
		return $this->templateFactory->createTemplate($class);
	}

}
