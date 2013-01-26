<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;

/**
 * Controller pro uvodni a informaci stranky
 *
 */
class UserController extends BaseController {
    
    const USER_REGISTRATION_AUTH_SALT = "CapHeC9a";

    /** @var TitleLoader */
    protected $titleLoader;
    protected $userTable;
    protected $authenticateTable;

    /**
     * Nastaví kontext contrloleru, Zde se pomocí Dependency Injection vloží do třídy instance služeb, které budou potřeba
     * Mezi služby se řadí také modely a DB modely
     * Je třeba nadefinovat modely v config.neon
     * @param app\services\TitleLoader $titleLoader
     */
    public function setContext(
    TitleLoader $titleLoader, app\models\authentication\AuthenticateTable $authenticateTable, app\models\authentication\UserTable $userTable) {

        $this->titleLoader = $titleLoader;
        $this->authenticateTable = $authenticateTable;
        $this->userTable = $userTable;
    }

    public function loginAction() {
        // Kontrola, zda už uživatel není přihlášen
        if ($this->user->isLoggedIn()) {
            Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector')->gotoRouteAndExit(array(), "eventList");
        }
        
        $this->template->title = $this->titleLoader->getTitle('Admin:Index:login');
        
        // Formulář
        $form = new LoginForm();
        $this->view->loginForm = $form;
    }

	public function fbLoginAction() {
		$this->redirect('userLogin');
	}
    
    public function logoutAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        
        Zend_Auth::getInstance()->clearIdentity();

        $this->_helper->redirector->gotoRouteAndExit(array(), "userLogin");
    }

    /**
     * Registrace nového uživatele
     */
    public function registerAction() {
        $this->template->title = $this->t($this->titleLoader->getTitle('Index:register'));

        // Formulář
        $form = new RegisterForm();
        $this->view->form = $form;

        // Zpracování požadavku na registraci
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) { // Validace prošla, jde se na registraci
                $formValues = $form->getValues();
                $this->doRegistration($formValues["email"], $formValues["password1"], $formValues["name"], $formValues["surname"]);

                // Přesměrování a zpráva
                $this->flashMessage("Vaše registrace byla úspěšná. Posledním krokem k dokončení registrace je ale ještě aktivace Vašeho účtu. Instrukce naleznete v e-mailu, který jsme Vám zaslali.", self::FLASH_INFO);
                $this->_helper->redirector->gotoRouteAndExit(array(), "eventList");
            }
        }
    }
    
    public function registerbygetAction() {
        $authToken = $this->_getParam("authToken");
        $email = $this->_getParam("email");
        $password = $this->_getParam("password");
        $name = explode(" ", $this->_getParam("name"));
        $activationRequired = $this->_getParam("activationRequired") == "true";
        
        $this->_helper->layout->disableLayout();
        Nette\Diagnostics\Debugger::$bar = FALSE;
        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=utf-8');
        
        try {
            // Check token
            $explodedEmail = explode("@", $email);
            $checkAuthToken = hash("sha256", $explodedEmail[0] . self::USER_REGISTRATION_AUTH_SALT . "@" . $explodedEmail[1]);
            if ($authToken != $checkAuthToken) {
                throw new Exception();
            }
            
            $this->doRegistration($email, $password, $name[0], $name[1], $activationRequired, true);
        } catch (Exception $ex) {
            $status = 0;
        }
        
        $this->template->status = $status;
    }
    
    private function doRegistration($email, $password, $name, $surname, $activationRequired = true, $isFinalPassword = false) {
        $finalPassword = $isFinalPassword ? new My_Password($password) : $password;

        // Uložit záznam do tabulky user
        $user = $this->userTable->createRow();
        $user->email = $email;
        $user->first_name = $name ;
        $user->last_name = $surname;
        $user->last_login_date = new Zend_Db_Expr("NOW()");
        $user->last_login_ip = $this->getRequest()->getServer('REMOTE_ADDR');
        $user->save();

        // Uložit záznam do tabulky authenticate
        $auth = $this->authenticateTable->createRow();
        $auth->created = new Zend_Db_Expr("NOW()");
        $auth->identity = $user->email;
        $auth->verification = $finalPassword->getDHash();
        $auth->user_id = $user->user_id;
        $auth->authenticate_provides_id = Application_Plugin_DbAuth::AUTHENTICATE_PROVIDE_EMAIL;
        $auth->active = $activationRequired ? 0 : 1;
        $auth->save();

        // Aktivační e-mail
        if ($activationRequired) {
            $text = "Dobrý den,\n";
            $text .= "zasíláme Vám registrační údaje na portálu myEvents.vse.cz.\n";
            $text .= "Váš účet je ještě třeba aktivovat. Aktivaci provedete kliknutím na odkaz, který je přiložený níže. Pokud Vám na odkaz nejde kliknout, překopírujte ho do adresního řádku svého webového prohlížeče.\n\n";
            $text .= "Přihlašovací e-mail: " . $auth->identity . "\n";
            $text .= "Heslo: " . $password . "\n";
            $text .= "Aktivační odkaz: http://" . $_SERVER['SERVER_NAME'] . "/aktivace/" . $auth->authenticate_id . "/" . substr($auth->verification, 0, 10) . "\n\n";
            $text .= "Doufáme, že se Vám bude na portálu MyEvents líbit a že pro vás bude užitečným :).";

            $mail = new Zend_Mail("utf-8");
            $mail->addTo($user->email, $user->first_name . " " . $user->last_name);
            $mail->setSubject("Registrace na MyEvents");
            $mail->setFrom("no-reply@vse.cz", "MyEvents");
            $mail->setBodyText($text);
            $mail->send();
        }
    }

    /**
     * Aktivace účtu uživatele
     */
    public function activateAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $id = $this->_getParam("id");
        $password = $this->_getParam("password");

        //Provedení aktivace
        $select = $this->authenticateTable->select();
        $select->where("authenticate_id = ?", $id);
        $select->where("SUBSTR(verification, 1, 10) = ?", $password);
        $auth = $this->authenticateTable->fetchRow($select);

        if ($auth != null) { // OK
            if ($auth->active) { // Již aktivní
                $messageText = "Účet s e-mailovou adresou " . $auth->identity . " již je aktivní.";
                $messageType = self::FLASH_ERROR;
            } else {
                $auth->active = 1;
                $auth->save();

                $messageText = "Účet s e-mailovou adresou " . $auth->identity . " byl aktivován. Nyní se můžete přihlásit.";
                $messageType = self::FLASH_INFO;
            }
        } else { // Fail
            $messageText = "Aktivace selhala. Kontaktujte prosím administrátora portálu MyEvents.";
            $messageType = self::FLASH_ERROR;
        }

        // Dokončení
        $this->flashMessage($messageText, $messageType);
        $this->_helper->redirector->gotoRouteAndExit(array(), "eventList");
    }

}

