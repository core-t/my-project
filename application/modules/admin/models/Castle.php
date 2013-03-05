<?php

class Admin_Model_Castle extends Coret_Model_ParentDb
{
    protected $_name = 'castle';
    protected $_primary = 'castleId';
    protected $_columns = array(
        'castleId' => array('nazwa' => 'Castle ID', 'typ' => 'tekst'),
        'name' => array('nazwa' => 'Nazwa', 'typ' => 'tekst'),
        'income' => array('nazwa' => 'Przychód', 'typ' => 'tekst'),
        'defensePoints' => array('nazwa' => 'Obrona', 'typ' => 'tekst'),
        'capital' => array('nazwa' => 'Stolica', 'typ' => 'tekst'),
        'x' => array('nazwa' => 'X', 'typ' => 'tekst'),
        'y' => array('nazwa' => 'Y', 'typ' => 'tekst'),
    );

    public function __construct($params, $id = 0)
    {
        parent::__construct(array(), $id);
    }

}

