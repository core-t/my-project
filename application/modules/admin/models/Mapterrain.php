<?php

class Admin_Model_Mapterrain extends Coret_Model_ParentDb
{
    protected $_name = 'mapterrain';
    protected $_primary = 'mapTerrainId';
    protected $_columns = array(
        'mapTerrainId' => array('label' => 'Terrain ID', 'type' => 'number', 'active' => array('form' => false)),
        'mapId' => array('label' => 'Map ID', 'type' => 'number'),
        'type' => array('label' => 'Typ', 'type' => 'varchar'),
        'flying' => array('label' => 'Latanie', 'type' => 'number'),
        'swimming' => array('label' => 'Pływanie', 'type' => 'number'),
        'walking' => array('label' => 'Chodzenie', 'type' => 'number'),
    );
    protected $_columns_lang = array(
        'name' => array('label' => 'Nazwa', 'type' => 'varchar')
    );
}

