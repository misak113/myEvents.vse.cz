<?php
namespace Zette\Caching;

use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;
use Nette\Reflection\ClassType;
use Nette\Utils\Strings;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 6.1.13
 * Time: 18:55
 * To change this template use File | Settings | File Templates.
 */
class CachedClass implements ICacheable
{
	/** @var \Zette\Caching\ICacheable */
	protected $instance;
	/** @var string */
	protected $cacheDir;
	/** @var \Nette\Caching\Cache */
	protected $cache;

	public function __construct($cacheDir, ICacheable $instance) {
		$this->cacheDir = $cacheDir;
		$this->instance = $instance;
	}

	public function __call($name, array $args) {
		$key = $this->createKey($name, $args);
		$cache = $this->getCache();

		$value = $cache->load($key);
		if ($value === null) {
			$value = call_user_func_array(array($this->instance, $name), $args);
			if ($value !== null) {
				$cache->save($key, $value);
			}
		}

		return $value;
	}

	protected function createKey() {
		$args = func_get_args();
		$json = json_encode($args);
		$key = sha1($json);

		return $key;
	}


	protected function getCache() {
		if ($this->cache == null) {
			$this->cache = new Cache(new FileStorage($this->cacheDir), $this->getNamespace());
		}

		return $this->cache;
	}

	protected function getNamespace() {
		$ref = new ClassType($this->instance);
		$name = $ref->getName();

		return Strings::webalize($name);
	}

}
