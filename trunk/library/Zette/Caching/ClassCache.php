<?php
namespace Zette\Caching;

use Nette\DI\Container;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 6.1.13
 * Time: 18:49
 * To change this template use File | Settings | File Templates.
 */
class ClassCache
{
	/** @var string */
	protected $cacheDir;

	public function injectContext(Container $context) {
		$this->cacheDir = $context->parameters['tempDir'].'/cache/class';
		if (!is_dir($this->cacheDir)) {
			@mkdir($this->cacheDir, 0775, true);
			if (!is_dir($this->cacheDir)) throw new \Nette\FileNotFoundException('Nešla vytvořit složka pro cachování "'.$this->cacheDir.'"');
		}
	}

	/**
	 * @param ICacheable $instance
	 * @return ICacheable
	 */
	public function getCachedInstance(ICacheable $instance) {
		return new CachedClass($this->cacheDir, $instance);
	}

}
