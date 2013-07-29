<?php

class Application_Model_MapTowers extends Game_Db_Table_Abstract
{
    protected $_name = 'maptowers';
    protected $_primary = 'mapTowerId';
//    protected $_sequence = "map_mapId_seq";
    protected $_db;
    protected $mapId;

    public function __construct($mapId)
    {
        $this->mapId = $mapId;
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function getMapTowers()
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId);
        try {
            $all = $this->_db->query($select)->fetchAll();
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }

        $mapTowers = array();

        foreach ($all as $val) {
            $mapTowers[$val['mapTowerId']] = $val;
        }

        return $mapTowers;
    }

}

