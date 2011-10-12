<?php

class Application_Form_Auth extends Zend_Form {

    public function init() {
        /* Form Elements & Other Definitions Here ... */
        $this->setMethod('post');
        $this->setAction('/login');
        $this->addElement('text', 'login', array(
            'label' => 'Login',
            'required' => true,
            'filters' => array('StringTrim')
        ));
        $this->addElement('password', 'password', array(
            'label' => 'Password',
            'required' => true,
            'filters' => array('StringTrim')
        ));
        $this->addElement('submit', 'submit', array('label' => 'Login'));
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
    }

}

