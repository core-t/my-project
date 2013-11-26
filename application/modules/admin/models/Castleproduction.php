<?php

class Admin_Model_Castleproduction extends Coret_Model_ParentDb
{
    protected $_name = 'castleproduction';
    protected $_primary = 'castleProductionId';
    protected $_sequence = 'castleproduction_castleProductionId_seq';

    protected $_columns = array(
        'castleId' => array('label' => 'Castle ID', 'type' => 'select'),
        'unitId' => array('label' => 'Unit ID', 'type' => 'select'),
        'time' => array('label' => 'Czas', 'type' => 'number'),
    );

}

