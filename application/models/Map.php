<?php

class Application_Model_Map extends Coret_Db_Table_Abstract
{
    protected $_name = 'map';
    protected $_primary = 'mapId';
    protected $_sequence = "map_mapId_seq";
    protected $mapId;

    public function __construct($mapId = 0, $db = null)
    {
        $this->mapId = $mapId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
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

    public function getMap()
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->mapId);

        return $this->selectRow($select);
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
            ->from($this->_name)
            ->order('mapId');

        $list = $this->selectAll($select);

        $maps = array();

        foreach ($list as $map) {
            $maps[$map['mapId']] = $map['name'];
        }

        return $maps;
    }

    public function getMinMapId()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'min("mapId")');

        return $this->selectOne($select);
    }
}

