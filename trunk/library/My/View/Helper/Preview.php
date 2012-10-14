<?php
/**
 * @author: Misak113
 * @date-created: 3.10.12
 */
class My_View_Helper_Preview extends Zend_View_Helper_Abstract
{
	const PREVIEW_LENGTH = 19;

	public function preview($text) {
		return substr($text, 0, self::PREVIEW_LENGTH) . (strlen($text) > self::PREVIEW_LENGTH ?'...' :'');
	}

}
