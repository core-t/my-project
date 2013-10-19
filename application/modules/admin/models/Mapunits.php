<?php

class Admin_Model_Mapunits extends Coret_Model_ParentDb
{
    protected $_name = 'mapunits';
    protected $_primary = 'mapUnitId';
    protected $_sequence = '';

    protected $_columns = array(
        'mapUnitId' => array('label' => 'Unit ID', 'type' => 'number'),
        'mapId' => array('label' => 'Map ID', 'type' => 'number'),
    );
    protected $_columns_lang = array(
        'name' => array('label' => 'Nazwa', 'type' => 'varchar')
    );

}

