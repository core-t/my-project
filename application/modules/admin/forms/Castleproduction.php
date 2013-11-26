<?php

class Admin_Form_Castleproduction extends Zend_Form
{

    public function init()
    {
        $mCastle = new Admin_Model_Castle();
        $castles = $mCastle->getCastles();

        $this->addElement('select', 'castleId',
            array(
                'label' => 'Castle ID',
                'multiOptions' => $castles,
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('Alnum')
                )
            )
        );

        $mUnit = new Admin_Model_Unit();
        $units = $mUnit->getUnits();

        $this->addElement('select', 'unitId',
            array(
                'label' => 'Unit ID',
                'multiOptions' => $units,
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('Alnum')
                )
            )
        );

        $this->addElement('text', 'time',
            array(
                'label' => 'Czas',
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('StringLength', false, array(1, 32)),
                    array('Alnum')
                )
            )
        );
    }

}

