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

        $multiOptions = array();
        for ($i = 2; $i <= $numberOfPlayers; $i++) {
            $multiOptions[$i] = $i;
        }

        $this->addElement('select', 'numberOfPlayers',
            array(
                'label' => $this->getView()->translate('Select number of players'),
                'multiOptions' => $multiOptions,
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(
                    array('Alnum'),
                    new Zend_Validate_Between(array('min' => 2, 'max' => $numberOfPlayers))
                )
            )
        );
    }

}

