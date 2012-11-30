<?php
namespace Zette\Config\Extensions;

use Nette\Config\CompilerExtension;
/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 30.11.12
 * Time: 21:52
 * To change this template use File | Settings | File Templates.
 */
class ZetteExtension extends CompilerExtension
{

	public $defaults = array(

	);

	public function loadConfiguration()	{
		$container = $this->getContainerBuilder();
		$config = $this->getConfig($this->defaults);

		$container->addDefinition('zette.injector')
			->setClass('Zette\DI\Injector');
		$container->addDefinition('zette.componentDispatcher')
				->setClass('Zette\UI\ComponentDispatcher');

	}

	public function beforeCompile() {
		$this->setupServices();
	}


	/**
	 * Nastaví inject na všechny služby
	 */
	protected function setupServices() {
		$defs = $this->getContainerBuilder()->getDefinitions();

		foreach ($defs as $name => &$def) {
			if ($def->autowired && in_array('inject', $def->tags) && $name != $this->prefix('injector')) {
				$def->addSetup('$this->getService(?)->tryInject($service);', array($this->prefix('injector')));
			}
		}
	}

}
