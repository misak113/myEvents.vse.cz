<?php
namespace Zette\Services;

use Zend_Controller_Plugin_Abstract;
use Zette\Services\Constants\FlashConstants;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 5.12.12
 * Time: 18:26
 * To change this template use File | Settings | File Templates.
 */
class PluginController extends Zend_Controller_Plugin_Abstract implements FlashConstants
{

	protected function flashMessage($message, $type = self::FLASH_INFO) {
		$this->_helper->flashMessenger->addMessage($message, $type);
	}
}
