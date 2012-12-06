<?php
namespace Zette\Application;

use Zend_Controller_Router_Route;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 6.12.12
 * Time: 23:29
 * To change this template use File | Settings | File Templates.
 */
class Route extends Zend_Controller_Router_Route
{
	/** @var string */
	protected $mask;

	public function __construct($route, $defaults = array(), $reqs = array(), \Zend_Translate $translator = null, $locale = null) {
		$this->mask = $route;
		parent::__construct($route, $defaults, $reqs, $translator, $locale);
	}

	public function getMask() {
		return $this->mask;
	}

}
