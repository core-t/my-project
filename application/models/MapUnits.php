<?php

class Application_Model_MapUnits extends Coret_Db_Table_Abstract
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

        return $this->selectOne($select);
    }

    public function getUnits()
    {
        $units = array(null);

        $select = $this->_db->select()
            ->from($this->_name, array('attackPoints', 'defensePoints', 'canFly', 'canSwim', 'cost', 'modMovesForest', 'modMovesHills', 'modMovesSwamp', 'numberOfMoves', $this->_primary))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->where('id_lang = ?', Zend_Registry::get('config')->id_lang)
            ->join($this->_name . '_Lang', $this->_name . ' . ' . $this->_db->quoteIdentifier($this->_primary) . ' = ' . $this->_db->quoteIdentifier($this->_name . '_Lang') . ' . ' . $this->_db->quoteIdentifier($this->_primary), 'name')
            ->order($this->_name . '.' . $this->_primary);

        foreach ($this->selectAll($select) as $unit) {
            $select = $this->_db->select()
                ->from($this->_name . '_Lang', 'name')
                ->where('id_lang = ?', Zend_Registry::get('id_lang'))
                ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $unit[$this->_primary]);

            $unit['name_lang'] = $this->selectOne($select);

            $units[$unit[$this->_primary]] = $unit;
        }

        return $units;
    }

}

