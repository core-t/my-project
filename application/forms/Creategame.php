<?php

class Application_Form_Creategame extends Zend_Form {

    public function init() {
       
        /* Form Elements & Other Definitions Here ... */
        $this->setMethod('post');
        $this->setAction('/gamesetup/create');
        $this->addElement('text', 'name',
        array(
         'label'=>'Nazwa gry',
         'required'=>true,
         'filters'=>array('StringTrim'),
         'validators'=>array(array('StringLength',false,array(3,256)))
         )
        );
        $this->addElement('text', 'numberOfPlayers',
        array(
         'label'=>'Ilość graczy',
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

