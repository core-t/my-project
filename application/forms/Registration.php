<?php

class Application_Form_Registration extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');

        $f = new Application_Form_Player();
        $this->addElements($f->getElements());

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

        $f = new Application_Form_Password();
        $this->addElements($f->getElements());

        $this->addElement('submit', 'submit', array('label' => $this->getView()->translate('Register')));
    }

}

