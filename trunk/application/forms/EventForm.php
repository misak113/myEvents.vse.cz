<?php

/**
 * Fromular pridani nove akce
 */
class EventForm extends Zend_Form {
    
    
    protected $categories;
    
    public function setCategories($categories){
        $this->categories = $categories;
    }

    /**
     * inicializace
     */
    public function init() {

        $this->setMethod('post');

        $this->addElement('text', 'fburl', array(
            'label' => 'Odkaz na Facebook: ',
            'class' => 'idleField',
            'filters' => array('StringTrim'),
            'validators' => array(
                array('regex', false, array(
                        'pattern' => '/^(http|https):\/\/www.facebook.com\/*/',
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

        $this->addElement('text', 'location', array(
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

        
        $options = array();
        foreach($this->categories->fetchAll() as $category){
            $options [$category->category_id] = $category->name;
        }
        $this->addElement('select', 'category', array(
            'label' => 'Kategorie: ',
            'required' => true,
            'filters' => array('StringTrim'),
            'multiOptions' => $options
        ));
        
        $this->addElement('textarea', 'shortinfo', array(
            'label' => 'Krátký popis (200 znaků): ',
            'filters' => array('StringTrim'),
            'attribs' => array(
                'maxlength' => '200',
                'rows' => '10'
                ),
            'validators' => array(
                array('StringLength', false, array(
                    'options' => array(0, 200),
                    'messages' => 'krátký popis musí být kratší než 200 znaků'
                ))
            )
        ));

        $this->addElement('textarea', 'longinfo', array(
            'label' => 'Popis: ',
            'filters' => array('StringTrim')
        ));


        $submit = new Zend_Form_Element_Submit('Uložit');
        $submit->setIgnore(true);
        $submit->setValue('Uložit');
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

    /**
     * Upravi formular do podoby editacniho formulare
     */
    public function setModifyMode() {
        $this->getElement('Uložit')->setLabel('Upravit');
    }

}

?>
