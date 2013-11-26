<?php

class Admin_Form_Mapcastles extends Zend_Form
{

    public function init()
    {
        $mMap = new Admin_Model_Map();
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
    }

}

