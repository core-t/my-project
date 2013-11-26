<?php

class Admin_Model_Mapcastlesproduction extends Coret_Model_ParentDb
{
    protected $_name = 'mapcastlesproduction';
    protected $_primary = 'mapCastleProductionId';
    protected $_sequence = 'mapcastlesproduction_mapCastleProductionId_seq';

    protected $_columns = array(
        'castleId' => array('label' => 'Castle ID', 'type' => 'select'),
        'unitId' => array('label' => 'Unit ID', 'type' => 'select'),
        'time' => array('label' => 'Czas', 'type' => 'number'),
    );

}

