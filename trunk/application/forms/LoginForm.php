<?php
/**
 * Přihlašovací formulář
 */
class LoginForm extends Zend_Form {

    /**
     * inicializace
     */
    public function init() {
        // Metoda
        $this->setMethod(self::METHOD_POST);

        
        // Přihlašovací e-mail
        $email = $this->createElement('text', 'email');
        $email->setLabel('E-mail');
        $email->addFilter('StringTrim');
        $email->setRequired(true);
        $this->addElement($email);

        // Heslo
        $password = $this->createElement('password', 'password');
        $password->setLabel('Heslo');
        $password->setRequired(true);
        $password->addValidator('StringLength', false, array(My_Password::$MIN_LENGTH));
        $this->addElement($password);
        
        // Submit
        $submit = new Zend_Form_Element_Submit("Přihlásit");
        $submit->setAttribs(array("class" => "btn btn-success btn-large", "name" => "login"));
        $submit->removeDecorator("DtDdWrapper");
        $this->addElement($submit);
    }

}

?>
