<?php

class Admin_Model_Castle extends Coret_Model_ParentDb
{
    protected $_name = 'castle';
    protected $_primary = 'castleId';
    protected $_columns = array(
        'castleId' => array('label' => 'Castle ID', 'typ' => 'varchar'),
        'name' => array('label' => 'Nazwa', 'typ' => 'varchar'),
        'income' => array('label' => 'Przychód', 'typ' => 'varchar'),
        'defensePoints' => array('label' => 'Obrona', 'typ' => 'varchar'),
        'capital' => array('label' => 'Stolica', 'typ' => 'checkbox'),
        'x' => array('label' => 'X', 'typ' => 'varchar'),
        'y' => array('label' => 'Y', 'typ' => 'varchar'),
    );

}

