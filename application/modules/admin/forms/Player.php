<?php

class Admin_Form_Player extends Zend_Form {

    public function init() {
        $this->setMethod('post');
        $this->addElement('text', 'login', array(
            'label' => 'Login',
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(1, 20)))
                )
        );
        $this->addElement('password', 'haslo', array(
            'label' => 'Hasło',
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(6, 20)))
                )
        );
        $this->addElement('hidden', 'id');
    }

}

