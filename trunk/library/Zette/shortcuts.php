<?php
/**
 * Created by JetBrains PhpStorm.
 * User: misak113
 * Date: 15.10.12
 * Time: 0:10
 * To change this template use File | Settings | File Templates.
 */

function _DBar($var, $title = null) {
	return \Nette\Diagnostics\Debugger::barDump($var, $title);
}