<?php

class Application_Model_Unit extends Coret_Db_Table_Abstract
{

    protected $_name = 'unit';
    protected $_primary = 'unitId';

    public function __construct($db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getUnitIdByName($name)
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_primary)
            ->where('name = ?', $name);

        return $this->selectOne($select);
    }

    public function getUnits()
    {
        $units = array(null);

        $select = $this->_db->select()
            ->from($this->_name, $this->_primary)
            ->where('id_lang = ?', Zend_Registry::get('config')->id_lang)
            ->join($this->_name . '_Lang', $this->_name . ' . ' . $this->_db->quoteIdentifier($this->_primary) . ' = ' . $this->_db->quoteIdentifier($this->_name . '_Lang') . ' . ' . $this->_db->quoteIdentifier($this->_primary), 'name')
            ->order($this->_name . '.' . $this->_primary);

        foreach($this->selectAll($select) as $unit) {
            $units[$unit[$this->_primary]] = $unit['name'];
        }

        return $units;
    }

}

