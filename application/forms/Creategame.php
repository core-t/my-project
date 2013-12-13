<?php

class Application_Form_Creategame extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');

        $mMap = new Application_Model_Map();

        $f = new Coret_Form_Select(array('name' => 'mapId', 'label' => $this->getView()->translate('Select map'), 'opt' => $mMap->getAllMapsList()));
        $this->addElements($f->getElements());

        $f = new Application_Form_NumberOfPlayers();
        $this->addElements($f->getElements());

        $timeLimits = array(
            0 => 'no limit',
            1 => '1 minute',
            2 => '2 minutes',
            3 => '3 minutes',
            5 => '5 minutes',
            10 => '10 minutes',
            20 => '20 minutes',
            30 => '30 minutes',
            60 => '1 hour',
            120 => '2 hours',
            180 => '3 hours',
            1440 => '1 day'
        );

        $f = new Coret_Form_Select(array('name' => 'timeLimit', 'label' => $this->getView()->translate('Select time limit'), 'opt' => $timeLimits));
        $this->addElements($f->getElements());

        $f = new Coret_Form_Number(array('name' => 'turnsLimit', 'label' => $this->getView()->translate('Turns limit')));
        $this->addElements($f->getElements());

        $this->addElement('submit', 'submit', array('label' => $this->getView()->translate('Create game')));
    }

}

