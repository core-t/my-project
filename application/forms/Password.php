<?php

class Application_Form_Password extends Zend_Form
{

    public function init()
    {
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
    }

}

