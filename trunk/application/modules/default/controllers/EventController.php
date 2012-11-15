<?php

use Zette\UI\BaseController;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 15.11.12
 * Time: 22:09
 * To change this template use File | Settings | File Templates.
 */
class EventController extends BaseController {


	public function detailAction() {
		$title = 'Název akce';
		$this->view->title = $title.' - '.$this->t($this->titleLoader->getTitle('Event:detail'));
	}
}
