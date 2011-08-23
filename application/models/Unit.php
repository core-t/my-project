<?php

class Application_Model_Unit extends Game_Db_Table_Abstract
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

    public function getUnits(){
        $units = array(null);
        try {
            $select = $this->_db->select()
                    ->from($this->_name)
                    ->order($this->_primary);
            $result = $this->_db->query($select)->fetchAll();
            foreach($result as $k=>$unit){
                $units[$k+1] = $unit;
            }
            return $units;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }
}

