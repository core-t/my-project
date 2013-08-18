<?php

class Admin_Model_Mapplayers extends Coret_Model_ParentDb
{
    protected $_name = 'mapplayers';
    protected $_primary = 'mapPlayerId';
    protected $_columns = array(
        'mapId' => array('label' => 'Map ID', 'type' => 'number'),
        'longName' => array('label' => 'Nazwa długa', 'type' => 'varchar'),
        'shortName' => array('label' => 'Nazwa krótka', 'type' => 'varchar'),
        'startOrder' => array('label' => 'Kolejność', 'type' => 'number'),
    );

}

