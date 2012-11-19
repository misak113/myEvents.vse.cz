<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 15.11.12
 * Time: 22:09
 * To change this template use File | Settings | File Templates.
 */
class EventController extends BaseController {


	/** @var TitleLoader */
	protected $titleLoader;


	/**
	 * Nastaví kontext contrloleru, Zde se pomocí Dependency Injection vloží do třídy instance služeb, které budou potřeba
	 * Mezi služby se řadí také modely a DB modely
	 * Je třeba nadefinovat modely v config.neon
	 * @param app\services\TitleLoader $titleLoader
	 */
	public function setContext(TitleLoader $titleLoader) {
		$this->titleLoader = $titleLoader;
	}


	public function detailAction() {
		$title = 'Název akce';
		$this->template->title = $title.' - '.$this->t($this->titleLoader->getTitle('Event:detail'));
	}

	/**
	 * Uvodni stranka
	 *
	 */
	public function listAction() {
		$this->template->title = $this->t($this->titleLoader->getTitle('Event:list'));
	}

}
