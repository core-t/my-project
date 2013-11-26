<?php

class Admin_Form_Mapcastles extends Zend_Form
{

    public function init()
    {
        $mMap = new Application_Model_Map();
        $maps = $mMap->getAllMapsList();

        $this->addElement('select', 'mapId',
            array(
                'label' => $this->getView()->translate('Map ID'),
                'multiOptions' => $maps,
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('Alnum')
                )
            )
        );

        $mCastle = new Application_Model_Castle();
        $castles = $mCastle->getCastles();

        $this->addElement('select', 'castleId',
            array(
                'label' => $this->getView()->translate('Castle ID'),
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

