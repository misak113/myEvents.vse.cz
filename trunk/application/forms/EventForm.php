<?php

/**
 * Fromular pridani nove akce
 */

class EventForm extends Zend_Form {
    
    /**
     * inicializace
     */
    public function init(){
        
        $this->setMethod('post');
        
        $this->addElement('text', 'fburl', array(
            'label' => 'Odkaz na Facebook: ',
            'class' => 'idleField',
            'filters' => array('StringTrim'),
            'validators' => array(
                array('regex', false, array(
                    'pattern' => '/^$(http|https):\/\/www.facebook.com\/*/',
                    'messages' => 'Vložte celý odkaz včetně http://')))
        ));
        
        $this->addElement('text', 'name', array(
            'label' => 'Název události: ',
            'class' => 'idleField',
            'required' => true,
            'filters' => array('StringTrim')
        ));
        
        $this->addElement('text', 'date', array(
            'label' => 'Datum: ',
            'class' => 'idleField',
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('regex', false, array(
                    'pattern' => '/^[0123]?\d\.[012]?\d\.201[2345]/',
                    'messages' => 'Vložte datum ve formátu dd.mm.rrrr'
                )
            ))
        ));
        
        $this->addElement('text', 'timestart', array(
            'label' => 'Čas začátku: ',
            'class' => 'idleField',
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('regex', false, array(
                    'pattern' => '/^[012]?\d:[012345]\d/',
                    'messages' => 'zadejte čas ve formátu hh:mm'
                )
            ))
        ));
        
        $this->addElement('text', 'timeend', array(
            'label' => 'Předpokládaný konec: ',
            'class' => 'idleField',
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(
                array('regex', false, array(
                    'pattern' => '/^[012]?\d:[012345]\d/',
                    'messages' => 'zadejte čas ve formátu hh:mm'
                )
            ))
            
        ));
        
        $this->addElement('text', 'place', array(
            'label' => 'Místo konání: ',
            'class' => 'idleField',
            'required' => true,
            'filters' => array('StringTrim')
        ));
        
        $this->addElement('text', 'capacity', array(
            'label' => 'Kapacita: ',
            'class' => 'idleField',
            'filters' => array('Digits'),
            'validators' => array(
                'Digits',
                array('validator' => 'GreaterThan', 'options' => array(0))
            )
        ));
        
        $this->addElement('select', 'category', array(
            'label' => 'Kategorie: ',
            'required' => true,
            'filters' => array('StringTrim'),
            'multiOptions' => array('', 'nacist', 'z', 'databaze')
        ));
        
        $this->addElement('textarea', 'longinfo', array(
            'label' => 'Popis: ',
            'filters' => array('StringTrim')
        ));
        
        
        $this->addElement('submit', 'save', array(
            'label' => 'Uložit',
            'class' => 'btn btn-success btn-large',
            'ignore' => true
        ));
        
    }
    
    
}
?>
