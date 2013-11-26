<?php

class Admin_Form_Login extends Zend_Form {

    public function init() {
        $this->setMethod('post');
        $this->setAction('/admin/login');
        $this->addElement('text', 'login', array(
                'label' => 'Identyfikator',
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(array('StringLength', false, array(3, 20)))
            )
        );
        $this->addElement('password', 'haslo', array(
                'label' => 'Hasło',
                'required' => true,
                'filters' => array('StringTrim')
            )
        );
        $this->addElement('submit', 'submit', array('label' => 'Zaloguj'));
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }

}

