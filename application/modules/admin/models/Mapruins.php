<?php

class Admin_Model_Mapruins extends Coret_Model_ParentDb
{
    protected $_name = 'mapruins';
    protected $_primary = 'mapRuinId';
    protected $_sequence = 'mapruins_mapRuinId_seq';

    protected $_columns = array(
        'mapRuinId' => array('label' => 'Ruin ID', 'type' => 'number'),
        'mapId' => array('label' => 'Map ID', 'type' => 'number'),
        'x' => array('label' => 'X', 'type' => 'number'),
        'y' => array('label' => 'Y', 'type' => 'number'),
    );

}

