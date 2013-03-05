<?php

class Admin_Form_Castle extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');

        $this->addElement('text', 'castleId', array(
                'label' => 'Castle ID:',
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array('Digits')
            )
        );

        $this->addElement('text', 'name', array(
                'label' => 'Nazwa:',
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(array('StringLength', false, array(1, 32)))
            )
        );

        $this->addElement('text', 'income', array(
                'label' => 'Przychód:',
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array('Digits')
            )
        );

        $this->addElement('text', 'defensePoints', array(
                'label' => 'Obrona:',
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(array('StringLength', false, array(1, 32)))
            )
        );

        $this->addElement('text', 'x', array(
                'label' => 'X:',
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array('Digits')
            )
        );

        $this->addElement('text', 'y', array(
                'label' => 'Y:',
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array('Digits')
            )
        );

        $this->addElement('checkbox', 'capital', array(
                'label' => 'Stolica:',
                'required' => false
            )
        );

//        $lang = new Coret_Form_Lang();
//        $this->addElements($lang->getElements());

        $this->addElement('hidden', 'id');

        $this->addElement('submit', 'submit', array('label' => 'Potwierdź'));
    }

}

