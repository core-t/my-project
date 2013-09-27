<?php

class Admin_Model_Artifactinventoryposition extends Coret_Model_ParentDb
{
    protected $_name = 'artifactinventoryposition';
    protected $_primary = 'artifactInventoryPositionId';
    protected $_columns = array(
        'artifactInventoryPositionId' => array('label' => 'Position ID', 'type' => 'number', 'active' => array('form' => false)),
        'name' => array('label' => 'Nazwa', 'type' => 'varchar'),
    );

}

