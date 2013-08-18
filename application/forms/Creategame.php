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

        $this->addElement('submit', 'submit', array('label' => $this->getView()->translate('Create game')));
    }

}

