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
		$container->addDefinition('zette.connectionPanel')
				->setClass('Zette\Database\Diagnostics\ConnectionPanel');
		$container->addDefinition('zette.timerPanel')
				->setClass('Zette\Diagnostics\TimerPanel')
				->addSetup('\Nette\Diagnostics\Debugger::$bar->addPanel($service);?', array('')) // @todo proč nejde normálně bez ?
				->addTag('run');

	}

	public function beforeCompile() {
		$this->setupServices();
	}


	/**
	 * Nastaví inject na všechny služby
	 */
	protected function setupServices() {
		$defs = $this->getContainerBuilder()->getDefinitions();

		/** @var \Nette\DI\ServiceDefinition $def  */
		foreach ($defs as $name => &$def) {

			// AutoInjecting
			if ($def->autowired && in_array('inject', $def->tags) && $name != $this->prefix('injector')) {
				$def->addSetup('$this->getService(?)->tryInject($service);', array($this->prefix('injector')));
			}

			// Model dependencies
			try {
				$class = $def->class ?$def->class :$def->factory->entity;
				$classReflection = \Nette\Reflection\ClassType::from($class);
				if ($classReflection->isSubclassOf('\Zend_Db_Table_Abstract')) {
					$def->addSetup('$this->getService(?)->trySetTableDependencies($service);', array($this->prefix('injector')));
				}
			} catch (\ReflectionException $e) {
				_dBar($e);
			}
		}
	}

}
