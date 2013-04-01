<?php

/**
 * Fromular pridani nove akce
 */
class EventForm extends Zend_Form {

    protected $categories;
	/** @var \app\models\events\TagTable */
    protected $tags;

    public function setCategories($categories) {
        $this->categories = $categories;
    }
    
    public function setTags($tags) {
    	$this->tags = $tags;
    }

    /**
     * inicializace
     */
    public function init() {

        $this->setMethod('post');
       

//        $file = new Zend_Form_Element_File('picture');
//        $file->setLabel('Obrázek')
//            ->setDestination(APP_DIR . '/../www/img/picture');
//        
//        $file->setDecorators(array(
//            'File', 
//            array('ViewScript', 
//                array('viewScript' => 'file.phtml', 'placement' => false))
//            )
//        );
//        $this->addElement($file);
        
        
        $fburl = new My_Form_Element_Url('fburl');
        $fburl->setLabel('Odkaz na facebook: ')
                ->setAttrib('class', 'idleField')
                ->addFilter('StringTrim')
                ->addValidators(array(
                    array('regex', false, array(
                            'pattern' => '/^(http|https):\/\/www.facebook.com\/*/',
                            'messages' => 'Vložte celý odkaz včetně http://')
                        )
                    )
        );
        $this->addElement($fburl);

        $this->addElement('text', 'name', array(
            'label' => 'Název události: ',
            'class' => 'idleField',
            'required' => true,
            'filters' => array('StringTrim')
        ));
        
        $this->addElement('hidden', 'picture');
        
        $date = new My_Form_Element_Date('date');
        $date->setLabel('Datum: ')
                ->setAttrib('class', 'idleField')
                ->setValue('rrrr-mm-dd')
                ->setRequired(true)
                ->addFilter('StringTrim')
                ->addValidators(array(
                    array('regex', false, array(
                            'pattern' => '/^201[2345]-[012]?\d-[0123]?\d$/',
                            'messages' => 'Vložte datum ve formátu rrrr-mm-dd'
                        )
                        )));
        $this->addElement($date);

        $timestart = new My_Form_Element_Time('timestart');
        $timestart->setLabel('Čas začátku: ')
                ->setAttrib('class', 'idleField')
                ->setValue('hh:mm')
                ->setRequired(true)
                ->addFilter('StringTrim')
                ->addValidators(array(
                    array('regex', false, array(
                            'pattern' => '/^[012]?\d:[012345]\d$/',
                            'messages' => 'zadejte čas ve formátu hh:mm'
                    ))
                ));
        $this->addElement($timestart);

        $timeend = new My_Form_Element_Time('timeend');
        $timeend->setLabel('Předpokládaný čas konce: ')
                ->setAttrib('class', 'idleField')
                ->setValue('hh:mm')
                ->setRequired(true)
                ->addFilter('StringTrim')
                ->addValidators(array(
                    array('regex', false, array(
                            'pattern' => '/^[012]?\d:[012345]\d$/',
                            'messages' => 'zadejte čas ve formátu hh:mm'
                    ))
                ));
        $this->addElement($timeend);

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
            'required' => 'true',
            'validators' => array(
                'Digits',
                array('validator' => 'GreaterThan', 'options' => array(0))
            )
        ));


        $options = array();
        foreach ($this->categories->fetchAll() as $category) {
            $options [$category->category_id] = $category->name;
        }
        $this->addElement('select', 'category', array(
            'label' => 'Kategorie: ',
            'required' => true,
            'filters' => array('StringTrim'),
            'multiOptions' => $options
        ));
        
        $options2 = array();
        foreach ($this->tags->getTags() as $tag) {
			$options2[$tag['tag_id']] = " " . $tag['name'];
        }
        $this->addElement('multiCheckbox', 'tags1', array(
        		'label' => 'Tagy: ',
        		'class' => 'checkBox',
        		'required' => false,
        		'multiOptions' => $options2,
        	    'separator' => PHP_EOL
        		)
        );
		$options2 = array();
		foreach ($this->tags->getPlaces() as $tag) {
			$options2[$tag['tag_id']] = " " . $tag['name'];
		}
		$this->addElement('multiCheckbox', 'places', array(
				'label' => 'Místa: ',
				'class' => 'checkBox',
				'required' => false,
				'multiOptions' => $options2,
				'separator' => PHP_EOL
			)
		);

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
        
        $public = new Zend_Form_Element_Checkbox('public');
        $public->setLabel('Publikovat');
        $this->addElement($public);


        $submit = new Zend_Form_Element_Submit('save');
        $submit->setIgnore(true);
        $submit->setValue('Uložit');
        $submit->setAttribs(array('class' => 'btn btn-success btn-large'));
        $submit->removeDecorator('DtDdWrapper');
        $this->addElement($submit);



        $this->setElementDecorators(array(
            'ViewHelper',
            //'Errors',
            array('Errors', array('tag' => 'span', 'class' => 'label label-important err')),
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
        $this->getElement('save')->setLabel('Upravit');
    }

}

?>
