<?php

class Admin_Form_Mapunits extends Zend_Form
{

    public function init()
    {
        $mMap = new Application_Model_Map();
        $maps = $mMap->getAllMapsList();

        $this->addElement('select', 'mapId',
            array(
                'label' => 'Map ID',
                'multiOptions' => $maps,
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
    }

}

