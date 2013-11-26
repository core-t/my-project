<?php

class Application_Model_Castle extends Coret_Db_Table_Abstract
{
    protected $_name = 'castle';
    protected $_primary = 'castleId';
    protected $_sequence = 'castle_castleId_seq';

    public function __construct($db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getCastles()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('castleId', 'name'));

        $castles = array();

        foreach ($this->selectAll($select) as $row) {
            $castles[$row['castleId']] = $row['name'];
        }

        return $castles;
    }

}

