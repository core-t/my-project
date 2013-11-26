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
            ->from(array('a' => $this->_name), 'null')
            ->join(array('b' => 'unit'), 'a."unitId"=b."unitId"', array('attackPoints', 'defensePoints', 'canFly', 'canSwim', 'cost', 'modMovesForest', 'modMovesHills', 'modMovesSwamp', 'numberOfMoves', 'unitId'))
            ->join(array('c' => 'unit_Lang'), 'b.' . $this->_db->quoteIdentifier('unitId') . ' = c.' . $this->_db->quoteIdentifier('unitId'), 'name')
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->where('id_lang = ?', Zend_Registry::get('config')->id_lang)
            ->order('a.' . $this->_db->quoteIdentifier('unitId'));

        foreach ($this->selectAll($select) as $unit) {
            $select = $this->_db->select()
                ->from('unit_Lang', 'name')
                ->where('id_lang = ?', Zend_Registry::get('id_lang'))
                ->where($this->_db->quoteIdentifier('unitId') . ' = ?', $unit['unitId']);

            $unit['name_lang'] = $this->selectOne($select);

            $units[$unit['unitId']] = $unit;
        }

        return $units;
    }

}

