<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 15.11.12
 * Time: 22:44
 * To change this template use File | Settings | File Templates.
 */
class Landing_IndexController extends BaseController
{
	const FILE_EMAILS = 'emails.txt';

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

	public function indexAction() {
		$this->view->title = $this->titleLoader->getTitle('Landing:Index:index');
		$this->_helper->layout->setLayout('landing');
	}

	public function emailSentAction() {

		// @todo
		$email = @$_POST['email'];

		$status = @file_put_contents(FILE_EMAILS, $email.";".time().";".$_SERVER['REMOTE_ADDR']."\n", FILE_APPEND);

		if ($status) {
			header('Location: /');
			setcookie('email_sent', 'sent', time()+5);
		} else {
			header('Location: /');
		}

	}

}
