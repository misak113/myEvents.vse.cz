<?php
namespace Zette\DI;

use Nette\Config\Configurator;
use Nette\DI\Container;
use Nette\Diagnostics\Debugger;
use Nette\Config\Compiler;
use Zette\Config\Extensions\ZetteExtension;
use Zette\Git\Helper;
use Zette\Diagnostics\TimerPanel;


define('APP_DIR', APPLICATION_PATH);
define('LIBS_DIR', realpath(APP_DIR.'/../library'));

require LIBS_DIR . '/Nette/loader.php';
require_once 'Zette/Diagnostics/TimerPanel.php';
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
		$logDir = APP_DIR . '/../log';
		$tempDir = APP_DIR . '/../temp';

		TimerPanel::start('create temp dirs');
		// Vytvoření temp složek, které jsou v ignoru
		$this->forceCreateDir($logDir);
		$this->forceCreateDir($tempDir);
		define('LOG_DIR', realpath($logDir));
		define('TEMP_DIR', realpath($tempDir));
		TimerPanel::stop('create temp dirs');

		TimerPanel::start('new Configurator');
		// Configure application
		$configurator = new Configurator;
		TimerPanel::stop('new Configurator');

		// Enable Nette Debugger for error visualisation & logging
		//$configurator->setDebugMode($configurator::AUTO);
		$configurator->enableDebugger(LOG_DIR);

		$debugMode = APPLICATION_ENV != 'production';

		if (isset($_COOKIE['debugMode']) && $_COOKIE['debugMode'] == 1) {
			$debugMode = true; // @todo fail? aby se mi v browseru u me zobrazovaly ladenky
		}

		Debugger::enable(!$debugMode);

		TimerPanel::start('createRobotLoader');
		// Enable RobotLoader - this will load all classes automatically
		$configurator->setTempDirectory(TEMP_DIR);
		$configurator->createRobotLoader()
				->addDirectory(APP_DIR)
				->addDirectory(LIBS_DIR)
				->register();
		TimerPanel::stop('createRobotLoader');


		TimerPanel::start('load configs');
		// Create Dependency Injection container from config.neon file
		$configurator->addConfig(__DIR__ . '/../Config/config.default.neon', Configurator::NONE);

		// Globální config
		$configurator->addConfig(APP_DIR . '/configs/config.neon', APPLICATION_ENV);

		// Branch config
		if($branch = Helper::parseRawGitDirectoryAndGetCurrentBranch()) {
			$branchConfig = realpath(APP_DIR . "/configs/branch/$branch.neon");
			if(file_exists($branchConfig)) {
				$configurator->addConfig($branchConfig, Configurator::NONE);
			}
		}

		// lokální config
		$configLocal = APP_DIR . '/configs/config.local.neon';
		$this->createIfNotExists($configLocal, APP_DIR . '/configs/config.local.neon.orig');
		$configurator->addConfig($configLocal, Configurator::NONE);
		TimerPanel::stop('load configs');


		$configurator->onCompile[] = function (Configurator $configurator, Compiler $compiler) {
			$compiler->addExtension('zette', new ZetteExtension);
		};

		TimerPanel::start('creating container');
		$this->context = $configurator->createContainer();
		TimerPanel::stop('creating container');

	}

	public function getContext() {
		return $this->context;
	}


	protected function forceCreateDir($path) {
		if (!@file_exists($path)) {
			_dBar('Created directory "'.$path.'"');
			@mkdir($path, 0777, true);
		}
		if (!@is_dir($path)) {
			return false;
		}
		return false;
	}

	protected function createIfNotExists($file, $templateFile) {
		if (!@file_exists($file)) {
			_dBar('Copied file "'.$templateFile.'" to "'.$file.'"');
			@copy($templateFile, $file);
		}
		if (!@is_file($file)) {
			return false;
		}
		return true;
	}

}
