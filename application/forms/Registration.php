<?php

class Application_Form_Registration extends Zend_Form
{

    public function init()
    {
        /* Form Elements & Other Definitions Here ... */
        $this->setMethod('post');

        $this->addElement('text', 'firstName', array(
                'label' => $this->getView()->translate('First name'),
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(array('StringLength', false, array(1, 256)))
            )
        );
        $this->addElement('text', 'lastName', array(
                'label' => $this->getView()->translate('Last name'),
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(array('StringLength', false, array(1, 256)))
            )
        );
        $this->addElement('text', 'login', array(
                'label' => $this->getView()->translate('Email'),
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('StringLength', false, array(3, 32)),
                    new Zend_Validate_Db_NoRecordExists(
                        array(
                            'table' => 'player',
                            'field' => 'login'
                        )
                    ))

            )
        );
        $this->addElement('password', 'password', array(
            'label' => $this->getView()->translate('Password'),
            'required' => true,
            'validators' => array(array('StringLength', false, array(6, 32)))
        ));
        $this->addElement('password', 'repeatPassword',
            array(
                'label' => $this->getView()->translate('Repeat password'),
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('identical', false, array('token' => 'password'))
                )
            )
        );
        $this->addElement('submit', 'submit', array('label' => $this->getView()->translate('Register')));
    }

}

