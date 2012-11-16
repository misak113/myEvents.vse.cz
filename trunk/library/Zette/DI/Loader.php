<?php
namespace Zette\DI;

use Nette\Config\Configurator;
use Nette\DI\Container;
use Nette\Diagnostics\Debugger;



define('APP_DIR', APPLICATION_PATH);
define('LIBS_DIR', realpath(APP_DIR.'/../library'));
define('LOG_DIR', realpath(APP_DIR . '/../log'));
define('TEMP_DIR', realpath(APP_DIR . '/../temp'));

require LIBS_DIR . '/Nette/loader.php';
require_once __DIR__.'/../shortcuts.php';

/**
 * Created by JetBrains PhpStorm.
 * User: misak113
 * Date: 15.10.12
 * Time: 0:22
 * To change this template use File | Settings | File Templates.
 */
class Loader
{
	/** @var Container */
	protected $context;



	public function load() {

// Configure application
		$configurator = new Configurator;

// Enable Nette Debugger for error visualisation & logging
//$configurator->setDebugMode($configurator::AUTO);
		$configurator->enableDebugger(LOG_DIR);

		$debugMode = APPLICATION_ENV != 'production';

		Debugger::enable(!$debugMode);

// Enable RobotLoader - this will load all classes automatically
		$configurator->setTempDirectory(TEMP_DIR);
		$configurator->createRobotLoader()
				->addDirectory(APP_DIR)
				->addDirectory(LIBS_DIR)
				->register();

// Create Dependency Injection container from config.neon file
		$configurator->addConfig(APP_DIR . '/configs/config.neon');
		$this->context = $configurator->createContainer();
	}

	public function getContext() {
		return $this->context;
	}

}
