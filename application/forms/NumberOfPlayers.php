<?php

class Application_Form_NumberOfPlayers extends Zend_Form
{

    public function init()
    {
        if (isset($this->_attribs['mapId'])) {
            $mapId = $this->_attribs['mapId'];
        } else {
            $mMap = new Application_Model_Map();
            $mapId = $mMap->getMinMapId();
        }

        $mMapPlayers = new Application_Model_MapPlayers($mapId);
        $numberOfPlayers = $mMapPlayers->getNumberOfPlayersForNewGame();

//        $multiOptions = array();
//        for ($i = 2; $i <= $numberOfPlayers; $i++) {
//            $multiOptions[$i] = $i;
//        }

        $f = new Coret_Form_Varchar(
            array(
                'name' => 'numberOfPlayers',
                'label' => $this->getView()->translate('Number of players'),
                'value' => $numberOfPlayers,
                'validators' => array(array('Alnum'), array('identical', false, array(array('token' => $numberOfPlayers, 'strict' => FALSE)))),
                'attr' => array('disabled' => 'disabled')
            )
        );
        $this->addElements($f->getElements());

//        $this->addElement('select', 'numberOfPlayers',
//            array(
//                'label' => $this->getView()->translate('Select number of players'),
//                'multiOptions' => $multiOptions,
//                'required' => true,
//                'filters' => array('StringTrim'),
//                'validators' => array(
//                    array('Alnum'),
//                    array('identical', false, array(array('token' => $numberOfPlayers, 'strict' => FALSE))
//                    new Zend_Validate_Between(array('min' => 2, 'max' => $numberOfPlayers))
//                    )
//                )
//            );
    }

}

