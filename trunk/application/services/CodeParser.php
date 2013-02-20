<?php
namespace app\services;

use Nette\Reflection\ClassType;
use ReflectionMethod;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 20.2.13
 * Time: 21:53
 * To change this template use File | Settings | File Templates.
 */
class CodeParser
{

	public function findAcceptablePrivileges() {
		$privileges = array();

		$resources = $this->findResources();
		foreach ($resources as $resource) {
			$ex = explode('.', $resource);
			$class = '';
			if (isset($ex[1])) {
				$class = ucfirst($ex[0]).'_';
				$ex[0] = $ex[1];
			}
			$class = $class.ucfirst($ex[0]).'Controller';
			$refClass = new ClassType($class);
			$methods = $refClass->getMethods(ReflectionMethod::IS_PUBLIC);
			foreach ($methods as $method) {
				if (preg_match('~^(.+)Action$~', $method->name, $m)) {
					$privileges[$resource][] = strtolower($m[1]);
				}
			}
		}

		return $privileges;
	}
	public function findResources() {
		$resources = array();

		$modulesDir = APP_DIR.'/modules';
		$modules = scandir($modulesDir);
		foreach ($modules as $module) {
			$controllersDir = $modulesDir.'/'.$module.'/controllers';
			if (!is_dir($controllersDir)) continue;
			$controllers = scandir($controllersDir);
			foreach ($controllers as $controller) {
				if (strstr($controller, 'Controller.php') === false) continue;
				$ctrlName = strtolower(str_replace('Controller.php', '', $controller));
				$resource = ($module != 'default' ?$module.'.' :'').$ctrlName;
				$resources[] = $resource;
			}
		}

		return $resources;
	}

	public function findPrivileges() {
		$privileges = array();

		$resources = $this->findResources();
		foreach ($resources as $resource) {
			$ex = explode('.', $resource);
			$class = '';
			if (isset($ex[1])) {
				$class = ucfirst($ex[0]).'_';
				$ex[0] = $ex[1];
			}
			$class = $class.ucfirst($ex[0]).'Controller';
			$refClass = new ClassType($class);
			$methods = $refClass->getMethods(ReflectionMethod::IS_PUBLIC);
			foreach ($methods as $method) {
				if (preg_match('~^(.+)Action$~', $method->name, $m)) {
					$privileges[] = strtolower($m[1]);
				}
			}
		}

		return $privileges;
	}
}
