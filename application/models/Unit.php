<?php

class Application_Model_Unit extends Warlords_Db_Table_Abstract
{
    protected $_name = 'unit';
    protected $_primary = 'unitId';
    protected $_db;

    public function __construct() {
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function getUnitIdByName($name) {
        try {
            $select = $this->_db->select()
                    ->from($this->_name, $this->_primary)
                    ->where('name = ?', $name);
            $result = $this->_db->query($select)->fetchAll();
            return $result[0][$this->_primary];
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }
}

