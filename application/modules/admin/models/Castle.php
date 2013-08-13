<?php

class Admin_Model_Castle extends Coret_Model_ParentDb
{
    protected $_name = 'mapcastles';
    protected $_primary = 'mapCastleId';
    protected $_columns = array(
        'mapCastleId' => array('label' => 'Castle ID', 'type' => 'number'),
        'mapId' => array('label' => 'Map ID', 'type' => 'number'),
        'name' => array('label' => 'Nazwa', 'type' => 'varchar'),
        'income' => array('label' => 'Przychód', 'type' => 'number'),
        'defense' => array('label' => 'Obrona', 'type' => 'number'),
        'capital' => array('label' => 'Stolica', 'type' => 'checkbox'),
        'x' => array('label' => 'X', 'type' => 'number'),
        'y' => array('label' => 'Y', 'type' => 'number'),
    );

}

