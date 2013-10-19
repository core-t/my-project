<?php

class Application_Model_MapUnits extends Game_Db_Table_Abstract
{

    protected $_name = 'mapunits';
    protected $_primary = 'mapUnitId';
    protected $mapId;

    public function __construct($mapId, $db = null)
    {
        $this->mapId = $mapId;
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
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->where('name = ?', $name);
        try {
            return $this->_db->fetchOne($select);
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getUnits()
    {
        $units = array(null);

        $select = $this->_db->select()
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->where('id_lang = 1')
            ->join($this->_name . '_Lang', $this->_name . ' . ' . $this->_db->quoteIdentifier($this->_primary) . ' = ' . $this->_db->quoteIdentifier($this->_name . '_Lang') . ' . ' . $this->_db->quoteIdentifier($this->_primary), 'name')
            ->order($this->_primary);

        $result = $this->selectAll($select);

        foreach ($result as $unit) {
            $units[$unit[$this->_primary]] = $unit;
        }

        return $units;
    }

}

