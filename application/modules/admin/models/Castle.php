<?php

class Admin_Model_Castle extends Coret_Model_ParentDb
{
    protected $_name = 'castle';
    protected $_primary = 'castleId';
    protected $_columns = array(
        'castleId' => array('label' => 'Castle ID', 'typ' => 'tekst'),
        'name' => array('label' => 'label', 'typ' => 'tekst'),
        'income' => array('label' => 'Przychód', 'typ' => 'tekst'),
        'defensePoints' => array('label' => 'Obrona', 'typ' => 'tekst'),
        'capital' => array('label' => 'Stolica', 'typ' => 'tekst'),
        'x' => array('label' => 'X', 'typ' => 'tekst'),
        'y' => array('label' => 'Y', 'typ' => 'tekst'),
    );

}

