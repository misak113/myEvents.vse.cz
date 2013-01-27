<?php

use Zette\UI\BaseController;
use app\services\TitleLoader;

/**
 * Controller pro uvodni a informaci stranky
 *
 */
class UserController extends BaseController {
    
    const USER_REGISTRATION_AUTH_SALT = "CapHeC9a";
    const PASSWORD_RECOVERY_AUTH_SALT = "bEb7QuCh";

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
        $this->_helper->layout->disableLayout();
        Nette\Diagnostics\Debugger::$bar = FALSE;
        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=utf-8');
        
        $authToken = $this->_getParam("authToken");
        $email = str_replace("&#47;", "/", $this->_getParam("email"));
        $password = $this->_getParam("password");
        $name = str_replace("&#47;", "/", $this->_getParam("name"));
        $activationRequired = $this->_getParam("activationRequired") != "false";
        
        // Name and surname
        $nameExploded = explode(" ", $name);
        $finalName = $nameExploded[0];
        
        $nameExploded[0] = null;
        $surname = trim(implode(" ", $nameExploded));
        
        try {
            // Check email existence
            $select = $this->authenticateTable->select();
            $select->where("identity = ?", $email);
            $select->where("authenticate_provides_id = 1");
            $auth = $this->authenticateTable->fetchRow($select);
            
            if ($auth != null) {
                $status = 2;
            } else {
                // Check token
                $explodedEmail = explode("@", $email);

                if (count($explodedEmail) != 2) {
                    throw new Exception();
                }

                $checkAuthToken = hash("sha256", $explodedEmail[0] . self::USER_REGISTRATION_AUTH_SALT . "@" . $explodedEmail[1]);
                if ($authToken != $checkAuthToken) {
                    throw new Exception();
                }

                $this->doRegistration($email, $password, $finalName, $surname, $activationRequired, true);
                $status = 1;
            }
        } catch (Exception $ex) {
            $status = 0;
        }
        
        $this->template->status = $status;
    }
    
    private function doRegistration($email, $password, $name, $surname, $activationRequired = true, $isFinalPassword = false) {
        $finalPassword = $isFinalPassword ?$password : new My_Password($password);

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
        $auth->verification = $isFinalPassword ? $finalPassword : $finalPassword->getDHash();
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
            $mail->setSubject("Registrace na myEvents");
            $mail->setFrom("no-reply@vse.cz", "myEvents");
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
            $messageText = "Aktivace selhala. Kontaktujte prosím administrátora portálu myEvents.";
            $messageType = self::FLASH_ERROR;
        }

        // Dokončení
        $this->flashMessage($messageText, $messageType);
        $this->_helper->redirector->gotoRouteAndExit(array(), "eventList");
    }
    
    public function recoverpasswordbygetAction() {
        $this->_helper->layout->disableLayout();
        Nette\Diagnostics\Debugger::$bar = FALSE;
        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=utf-8');
        
        $authToken = $this->_getParam("authToken");
        $email = $this->_getParam("email");
        
        // Check auth token
        try {
            $explodedEmail = explode("@", $email);

            if (count($explodedEmail) != 2) {
                throw new Exception();
            }

            $checkAuthToken = hash("sha256", $explodedEmail[0] . self::PASSWORD_RECOVERY_AUTH_SALT . "@" . $explodedEmail[1]);
            echo $authToken . "\n" . $checkAuthToken;
            if ($authToken != $checkAuthToken) {
                throw new Exception();
            }
            
            // Obnovit..
            $status = $this->recoverAuthentication($email) ? 1 : 0;
        } catch (Exception $ex) {
            $status = 0;
        }
        
        $this->template->status = $status;
    }

    private function recoverAuthentication($email) {
        $select = $this->authenticateTable->select();
        $select->where("identity = ?", $email);
        $select->where("authenticate_provides_id = 1");
        $select->where("active = 1");
        $auth = $this->authenticateTable->fetchRow($select);
        
        // Kontrola existence
        if ($auth == null) {
            return false;
        }
        
        // Vygenerovat nové heslo
        try {
            $password = new My_Password();

            // Uložit do DB
            $auth->recovered_verification = $password->getDHash();
            $this->authenticateTable->update($auth);

            // Odeslat email
            $text = "Dobrý den,";
            $text .= "obdrželi jsme žádost o obnovení vašeho hesla k účtu na portálu myEvents. Heslo je uvedeno níže.";
            $text .= "Pokud jste o obnovení hesla nežádal, jednoduše tento e-mail ignorujte a používejte k přihlašování nadále své původní heslo.";
            $text .= "\n\nVaše nové heslo: " . $password->getNonHashForm();
            $text .= "\n\n\nS pozdravem\ntým myEvents";

            $mail = new Zend_Mail("utf-8");
            $mail->addTo($email);
            $mail->setSubject("Obnovení ztraceného hesla");
            $mail->setFrom("no-reply@vse.cz", "myEvents");
            $mail->setBodyText($text);
            $mail->send();
            
            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
}

