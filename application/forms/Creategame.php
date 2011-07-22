<?php

class Application_Form_Creategame extends Zend_Form {

    public function init() {

        /* Form Elements & Other Definitions Here ... */
        $this->setMethod('post');
        $this->setAction('/gamesetup/create');

        $this->addElement('text', 'numberOfPlayers',
        array(
         'label'=>'Number of players',
         'required'=>true,
         'filters'=>array('StringTrim'),
         'validators'=>array(
                 array('Alnum'),
                 new Zend_Validate_Between(array('min' => 1, 'max' => 4))
             )
         )
        );
        $this->addElement('submit', 'submit', array('label' => 'Stwórz grę'));
    }

}

