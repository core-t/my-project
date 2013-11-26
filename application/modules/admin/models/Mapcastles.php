<?php

class Admin_Model_Mapcastles extends Coret_Model_ParentDb
{
    protected $_name = 'mapcastles';
    protected $_primary = 'mapCastleId';
    protected $_sequence = 'mapcastles_mapCastleId_seq';
    protected $_columns = array(
        'castleId' => array('label' => 'Castle ID', 'type' => 'number'),
        'mapId' => array('label' => 'Map ID', 'type' => 'number'),
    );

}

