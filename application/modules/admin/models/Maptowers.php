<?php

class Admin_Model_Maptowers extends Coret_Model_ParentDb
{
    protected $_name = 'maptowers';
    protected $_primary = 'mapTowerId';
    protected $_sequence = 'maptowers_mapTowerId_seq';

    protected $_columns = array(
        'mapTowerId' => array('label' => 'Tower ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'mapId' => array('label' => 'Map ID', 'type' => 'number'),
        'x' => array('label' => 'X', 'type' => 'number'),
        'y' => array('label' => 'Y', 'type' => 'number'),
    );

}

