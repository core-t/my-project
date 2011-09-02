<?php

class Application_Form_Createmap extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
        $this->setMethod('post');
        $this->setAction('/editor/create');

        $this->addElement('text', 'name',
        array(
         'label'=>'Map name',
         'required'=>true,
         'filters'=>array('StringTrim'),
         'validators'=>array(
                 array('Alnum')
             )
         )
        );
        $this->addElement('text', 'mapWidth',
        array(
         'label'=>'Map width (50 - 400)',
         'required'=>true,
         'filters'=>array('StringTrim'),
         'validators'=>array(
                 array('Alnum'),
                 new Zend_Validate_Between(array('min' => 50, 'max' => 400))
             )
         )
        );
        $this->addElement('text', 'mapHeight',
        array(
         'label'=>'Map height (50 - 400)',
         'required'=>true,
         'filters'=>array('StringTrim'),
         'validators'=>array(
                 array('Alnum'),
                 new Zend_Validate_Between(array('min' => 50, 'max' => 400))
             )
         )
        );
        $this->addElement('submit', 'submit', array('label' => 'Create map'));
    }


}

