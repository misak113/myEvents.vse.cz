<?php
namespace app\services;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 15.11.12
 * Time: 20:41
 * To change this template use File | Settings | File Templates.
 */
class TitleLoader
{
	const DEFAULT_TITLE = 'myEvents VŠE - najdi si zábavu na škole';

	/** @var array */
	protected $titles = array(
		'Index:index' => 'Akce',
		'Index:about' => 'O nás',
		'Event:detail' => 'Podrobnosti akce',
	);

	/**
	 * @param string $methodName
	 * @return string
	 */
	public function getTitle($methodName, $prepend = false) {
		$title = (isset($this->titles[$methodName]) ?$this->titles[$methodName].' - ' :'') . self::DEFAULT_TITLE;
		$prepend && $title.= $prepend.' - ';

		return $title;
	}

}
