<?php

class Admin_Model_Mapunits extends Coret_Model_ParentDb
{
    protected $_name = 'mapunits';
    protected $_primary = 'mapUnitId';
    protected $_sequence = 'unit_unitId_seq';

    protected $_columns = array(
        'mapUnitId' => array('label' => 'Unit ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'mapId' => array('label' => 'Map ID', 'type' => 'select'),
        'unitId' => array('label' => 'Unit ID', 'type' => 'select'),
    );

}

