<?php

class Application_Form_Registration extends Zend_Form {

    public function init() {
        /* Form Elements & Other Definitions Here ... */
        $this->setMethod('post');
        $this->setAction('/login/registration');
        $this->addElement('text', 'firstName', array(
            'label' => 'First name',
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(1, 256)))
                )
        );
        $this->addElement('text', 'lastName', array(
            'label' => 'Last name',
            'required' => true,
            'filters' => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(1, 256)))
                )
        );
        $this->addElement('text', 'login', array(
            'label' => 'Login',
            'required' => true,
            'filters' => array('StringTrim'),
            'validators'=>array(
                array('StringLength',false,array(3,32)),
                new Zend_Validate_Db_NoRecordExists(
                    array(
                        'table' => 'player',
                        'field' => 'login'
                    )
                ))

            )
        );
        $this->addElement('password', 'password', array(
            'label' => 'Password',
            'required' => true,
            'validators' => array(array('StringLength', false, array(6, 32)))
        ));
        $this->addElement('password', 'repeatPassword',
        array(
        'label'=>'Repeat password',
        'required'=>true,
        'filters'=>array('StringTrim'),
        'validators'=>array(
                array('identical', false, array('token' => 'password'))
            )
        )
        );
        $this->addElement('submit', 'submit', array('label' => 'Register'));
    }

}

