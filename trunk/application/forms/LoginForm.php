<?php
/**
 * Fromular pridani nove akce
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
        $password->addFilter('StringTrim');
        $password->setRequired(true);
        $this->addElement($password);
        
        // Submit
        $submit = new Zend_Form_Element_Submit("Přihlásit");
        $submit->setAttribs(array("class" => "btn btn-success btn-large", "name" => "login"));
        $submit->removeDecorator("DtDdWrapper");
        $this->addElement($submit);
    }

}

?>
