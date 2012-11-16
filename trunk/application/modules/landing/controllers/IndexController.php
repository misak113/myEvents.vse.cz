<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;
use app\models\EmailTable;

/**
 * Created by JetBrains PhpStorm.
 * User: Michael
 * Date: 15.11.12
 * Time: 22:44
 * To change this template use File | Settings | File Templates.
 */
class Landing_IndexController extends BaseController
{

	/** @var TitleLoader */
	protected $titleLoader;
	/** @var EmailTable */
	protected $emailTable;

	/**
	 * Nastaví kontext contrloleru, Zde se pomocí Dependency Injection vloží do třídy instance služeb, které budou potřeba
	 * Mezi služby se řadí také modely a DB modely
	 * Je třeba nadefinovat modely v config.neon
	 * @param app\services\TitleLoader $titleLoader
	 */
	public function setContext(TitleLoader $titleLoader, EmailTable $emailTable) {
		$this->titleLoader = $titleLoader;
		$this->emailTable = $emailTable;
	}

	public function indexAction() {
		$this->view->title = $this->titleLoader->getTitle('Landing:Index:index');
		$this->_helper->layout->setLayout('landing');
	}

	public function sentAction() {

		$email = $this->getRequest()->getParam('email');
		if (!$email) {
			$this->message($this->t('Email je třeba vyplnit!'), 'error');
			$this->redirect($this->url(array(), 'landing'));
		}

		$emailRow = $this->emailTable->createRow();
		$emailRow->setEmail($email);
		$emailRow->setUserAgent(@$_SERVER['HTTP_USER_AGENT']);
		$emailRow->setRemoteAddr($this->getRequest()->getServer('REMOTE_ADDR'));
		$emailRow->setRegistered(date('Y-m-d H:i:s'));
		$status = $emailRow->save();

		if ($status) {
			$this->message($this->t('E-mail byl odeslán a uložen!'));
		}

		$this->redirect($this->url(array(), 'landing'));
	}

	protected function message($text, $type = 'info') {
		setcookie('email_sent_message', $text, time()+5, '/');
		setcookie('email_sent_type', $type, time()+5, '/');
	}

}
