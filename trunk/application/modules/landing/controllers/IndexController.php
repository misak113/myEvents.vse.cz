<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;
use app\models\newsletter\EmailTable;

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
		$this->template->title = $this->titleLoader->getTitle('Landing:Index:index');
		$this->setLayout('landing');
	}

	public function sentAction() {

		$email = $this->getRequest()->getParam('email');
		if (!$email) {
			$this->flashMessage($this->t('Email je třeba vyplnit!'), self::FLASH_ERROR);
			$this->redirect($this->url(array(), 'landing'));
		}

		$emailRow = $this->emailTable->createRow();
		$emailRow->setEmail($email);
		$emailRow->setUserAgent(@$_SERVER['HTTP_USER_AGENT']);
		$emailRow->setRemoteAddr($this->getRequest()->getServer('REMOTE_ADDR'));
		$emailRow->setRegistered(date('Y-m-d H:i:s'));
		$status = $emailRow->save();

		if ($status) {
			$this->flashMessage($this->t('E-mail byl odeslán a uložen!'));
		} else {
			$this->flashMessage($this->t('Při ukládání e-mailu do databáze došlo k chybě!'), self::FLASH_ERROR);
		}

		$this->redirect($this->url(array(), 'landing'));
	}

}
