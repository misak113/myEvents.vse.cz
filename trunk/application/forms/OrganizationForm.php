<?php

/**
 * Fromular editace organizace
 */
class OrganizationForm extends Zend_Form {

    /**
     * inicializace
     */
    public function init() {

        $this->setMethod('post');

        $this->addElement('text', 'name', array(
            'label' => 'Název organizace: ',
            'class' => 'idleField',
            'required' => true,
            'filters' => array('StringTrim')
        ));
        
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('E-mail: ')
                ->setAttrib('class', 'idleField')
                ->addFilter('StringTrim')
                ->addValidators(array(new Zend_Validate_EmailAddress()));
     
        $this->addElement($email);
        
        $this->addElement('text', 'website', array(
            'label' => 'Stránka: ',
            'class' => 'idleField',
            'filters' => array('StringTrim')
        ));
        
        $fburl = new My_Form_Element_Url('fb_url');
        $fburl->setLabel('Odkaz na facebook: ')
                ->setAttrib('class', 'idleField')
                ->addFilter('StringTrim')
                ->addValidators(array(
                    array('regex', false, array(
                            'pattern' => '/^(http|https):\/\/(www.)?facebook.com\/*/',
                            'messages' => 'Vložte celý odkaz včetně http://')
                        )
                    )
        );
        $this->addElement($fburl);


        $this->addElement('textarea', 'info', array(
            'label' => 'Popis: ',
            'filters' => array('StringTrim')
        ));

        $submit = new Zend_Form_Element_Submit('Upravit');
        $submit->setIgnore(true);
        $submit->setValue('Upravit');
        $submit->setAttribs(array('class' => 'btn btn-success btn-large'));
        $submit->removeDecorator('DtDdWrapper');
        $this->addElement($submit);



        $this->setElementDecorators(array(
            'ViewHelper',
            //'Errors',
            array('Errors', array('tag' => 'span', 'class' => 'label label-important')),
            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span2')),
            array('Label', array('tag' => 'div', 'class' => 'span2')),
            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'row'))
        ));

        $submit->setDecorators(array(
            'ViewHelper',
            array(array('data' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span2')),
            array(array('emptyrow' => 'HtmlTag'), array('tag' => 'div', 'class' => 'span2', 'placement' => 'PREPEND')),
            array(array('row' => 'HtmlTag'), array('tag' => 'div', 'class' => 'row'))
        ));

        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'div')),
            'Form'));
    }

}

?>
