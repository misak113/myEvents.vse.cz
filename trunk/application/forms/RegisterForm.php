<?php

/**
 * Fromulář pro registraci nových uživatelů
 */
class RegisterForm extends Zend_Form {
    
    protected $requiredValidator;
    protected $emailValidator;
    protected $passwordLengthValidator;
    protected $identicalValidator;

    /**
     * inicializace
     */
    public function init() {
        // Metoda
        $this->setMethod(self::METHOD_POST);
        
        // Validátory
        $this->initValidators();

        // E-mail
        $email = $this->createElement("text", "email");
        $email->setLabel("E-mail *");
        $email->addFilter("StringTrim");
        $email->setRequired(true);
        $email->addValidator($this->requiredValidator);
        $email->addValidator($this->emailValidator);
        $this->addElement($email);

        // Heslo
        $password1 = $this->createElement("password", "password1");
        $password1->setLabel("Heslo *");
        $password1->setRequired(true);
        $password1->addValidator($this->requiredValidator);
        $password1->addValidator($this->passwordLengthValidator);
        $this->addElement($password1);

        // Heslo znovu
        $password2 = $this->createElement("password", "password2");
        $password2->setLabel("Heslo znovu *");
        $password2->setRequired(true);
        $password2->addValidator($this->requiredValidator);
        $password2->addValidator($this->passwordLengthValidator);
        $password2->addValidator($this->identicalValidator);
        $this->addElement($password2);

        // Jméno
        $name = $this->createElement("text", "name");
        $name->setLabel("Jméno *");
        $name->addFilter("StringTrim");
        $name->addValidator($this->requiredValidator);
        $name->setRequired(true);
        $this->addElement($name);

        // Přijmení
        $surname = $this->createElement("text", "surname");
        $surname->setLabel("Přijmení *");
        $surname->addFilter("StringTrim");
        $surname->setRequired(true);
        $surname->addValidator($this->requiredValidator);
        $this->addElement($surname);

        // Submit
        $submit = new Zend_Form_Element_Submit("userRegister");
        $submit->setAttribs(array("class" => "btn btn-success btn-large", "name" => "register"));
        $submit->setLabel("Odeslat registraci");
        $submit->removeDecorator("DtDdWrapper");
        $this->addElement($submit);
    }

    private function initValidators() {
        // Neprázdná hodnota
        $this->requiredValidator = new Zend_Validate_NotEmpty();
        $this->requiredValidator->setMessage("Toto pole je potřeba vyplnit");

        // Minimální délka hesla
        $this->passwordLengthValidator = new Zend_Validate_StringLength(array(My_Password::$MIN_LENGTH));
        $this->passwordLengthValidator->setMessage("Heslo musí být nejméně " . (string) My_Password::$MIN_LENGTH . " znaků dlouho");
        
        // Shodná hesla
        $this->identicalValidator = new Zend_Validate_Identical(array("token" => "password1"));
        $this->identicalValidator->setMessage("Kontrolní heslo se neshoduje se zadaným");
        
        // E-mail
        $this->emailValidator = new Zend_Validate_EmailAddress();
        $this->emailValidator->setMessage("Zadaný e-mail není ve správném formátu");
    }
}

?>
