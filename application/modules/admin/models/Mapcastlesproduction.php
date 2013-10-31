<?php

class Admin_Model_Mapcastlesproduction extends Coret_Model_ParentDb
{
    protected $_name = 'mapcastlesproduction';
    protected $_primary = 'mapCastleProductionId';
    protected $_sequence = 'mapcastlesproduction_mapCastleProductionId_seq';

    protected $_columns = array(
        'mapCastleId' => array('label' => 'Castle ID', 'type' => 'number'),
        'unitId' => array('label' => 'Unit ID', 'type' => 'number'),
        'time' => array('label' => 'Czas', 'type' => 'number'),
        'cost' => array('label' => 'Koszt', 'type' => 'number'),
    );

}

