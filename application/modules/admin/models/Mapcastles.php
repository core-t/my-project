<?php

class Admin_Model_Mapcastles extends Coret_Model_ParentDb
{
    protected $_name = 'mapcastles';
    protected $_primary = 'mapCastleId';
    protected $_columns = array(
        'mapCastleId' => array('label' => 'ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'castleId' => array('label' => 'Castle ID', 'type' => 'number'),
        'mapId' => array('label' => 'Map ID', 'type' => 'number'),
    );

}

