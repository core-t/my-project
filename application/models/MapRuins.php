<?php

class Application_Model_MapRuins extends Game_Db_Table_Abstract
{
    protected $_name = 'mapruins';
    protected $_primary = 'mapRuinId';
//    protected $_sequence = "map_mapId_seq";
    protected $_db;
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

    public function getMapRuins()
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId);
        try {
            $all = $this->_db->query($select)->fetchAll();
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }

        $ret = array();

        foreach ($all as $val) {
            $ret[$val[$this->_primary]] = $val;
        }

        return $ret;
    }

}

