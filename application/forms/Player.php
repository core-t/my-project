<?php

class Application_Form_Player extends Zend_Form
{

    public function init()
    {
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
    }

}

