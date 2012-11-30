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
            'filters' => array('StringTrim')
//            TODO: validace url
        ));
        
        $this->addElement('text', 'name', array(
            'label' => 'Název události: ',
            'class' => 'idleField',
            'required' => true,
            'filters' => array('StringTrim')
        ));
        
        $this->addElement('text', 'timestart', array(
            'label' => 'Začátek: ',
            'class' => 'idleField',
            'required' => true
//            TODO: validace datum
        ));
        
        $this->addElement('text', 'timeend', array(
            'label' => 'Předpokládaný konec: ',
            'class' => 'idleField'
//            TODO: validace datum
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
            'filters' => array('StringTrim')
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
