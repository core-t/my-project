<?php

class Admin_Model_Castle extends Coret_Model_ParentDb
{
    protected $_name = 'castle';
    protected $_primary = 'castleId';
    protected $_columns = array(
        'castleId' => array('label' => 'ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'name' => array('label' => 'Nazwa', 'type' => 'varchar'),
        'income' => array('label' => 'Przychód', 'type' => 'number'),
        'defense' => array('label' => 'Obrona', 'type' => 'number'),
        'capital' => array('label' => 'Stolica', 'type' => 'checkbox'),
        'x' => array('label' => 'X', 'type' => 'number'),
        'y' => array('label' => 'Y', 'type' => 'number'),
    );

    public function getCastles()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('castleId', 'name'))
            ->order('name');

        $castles = array();

        foreach ($this->selectAll($select) as $row) {
            $castles[$row['castleId']] = $row['name'];
        }

        return $castles;
    }

}

