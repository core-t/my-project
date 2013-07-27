<?php

class Application_Model_MapFields extends Game_Db_Table_Abstract
{
    protected $_name = 'mapFields';
    protected $_primary = 'mapId';
    protected $_sequence = "map_mapId_seq";
    protected $_db;
    protected $mapId;

    public function __construct($mapId = 0)
    {
        $this->mapId = $mapId;
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function createMap($params, $playerId)
    {
        $data = array(
            'name' => $params['name'],
            'mapWidth' => $params['mapWidth'],
            'mapHeight' => $params['mapHeight'],
            'playerId' => $playerId
        );

        $this->_db->insert($this->_name, $data);
        $seq = $this->_db->quoteIdentifier($this->_sequence);
        return $this->_db->lastSequenceId($seq);
    }

    public function getPlayerMapList($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where('"playerId" = ?', $playerId);
        try {
            return $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

//    public function getMapByPlayerId($playerId)
//    {
//        $select = $this->_db->select()
//            ->from($this->_name)
//            ->where('"' . $this->_primary . '" = ?', $this->mapId)
//            ->where('"playerId" = ?', $playerId);
//        try {
//            return $this->_db->fetchOne($select);
//        } catch (PDOException $e) {
//            throw new Exception($select->__toString());
//        }
//    }

    public function getMap()
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->mapId);
        try {
            return $this->_db->fetchRow($select);
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getMapName()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'name')
            ->where('"' . $this->_primary . '" = ?', $this->mapId);
        try {
            return $this->_db->fetchOne($select);
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getAllMapsList()
    {
        $select = $this->_db->select()
            ->from($this->_name);
        try {
            $list = $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }

        $maps = array();

        foreach ($list as $map) {
            $maps[$map['mapId']] = $map['name'];
        }

        return $maps;
    }
}

