<?php

use Zette\UI\BaseController;

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

	public function indexAction() {

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
