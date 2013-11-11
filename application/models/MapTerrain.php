<?php

class Application_Model_MapTerrain extends Coret_Db_Table_Abstract
{

    protected $_name = 'mapterrain';
    protected $_primary = 'mapTerrainId';
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

    public function getTerrain()
    {
        $terrain = array();

        $select = $this->_db->select()
            ->from($this->_name, array('flying', 'swimming', 'walking', 'type'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->where('id_lang = ?', Zend_Registry::get('id_lang'))
            ->join($this->_name . '_Lang', $this->_name . ' . ' . $this->_db->quoteIdentifier($this->_primary) . ' = ' . $this->_db->quoteIdentifier($this->_name . '_Lang') . ' . ' . $this->_db->quoteIdentifier($this->_primary), 'name')
            ->order($this->_name . '.' . $this->_primary);

        foreach ($this->selectAll($select) as $row) {
            $terrain[$row['type']] = $row;
        }

        return $terrain;
    }

}

