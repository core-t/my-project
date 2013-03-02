<?php

class Application_Form_Creategame extends Zend_Form
{

    public function init()
    {

        /* Form Elements & Other Definitions Here ... */
        $this->setMethod('post');

        $this->addElement('select', 'numberOfPlayers',
            array(
                'label' => 'Select number of players',
                'multiOptions' => array(2 => 2, 3 => 3, 4 => 4),
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('Alnum'),
                    new Zend_Validate_Between(array('min' => 2, 'max' => 4))
                )
            )
        );
        $this->addElement('submit', 'submit', array('label' => 'Create game'));
    }

}

