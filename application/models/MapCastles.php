<?php

class Application_Model_MapCastles extends Coret_Db_Table_Abstract
{
    protected $_name = 'mapcastles';
    protected $_primary = 'mapCastleId';
    protected $_sequence = 'mapcastles_mapCastleId_seq';
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

    public function getMapCastles()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), null)
            ->join(array('b' => 'castle'), 'a."castleId"=b."castleId"', array('castleId', 'x', 'y', 'name', 'income', 'capital', 'defense'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId);

        $castles = $this->selectAll($select);

        $mapCastles = array();

        foreach ($castles as $val) {
            $mapCastles[$val['castleId']] = $val;
            $mapCastles[$val['castleId']]['defensePoints'] = $val['defense'];
            $mapCastles[$val['castleId']]['position'] = array('x' => $val['x'], 'y' => $val['y']);
        }

        return $mapCastles;
    }

    public function getDefaultStartPositions()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), null)
            ->join(array('b' => 'castle'), 'a."castleId"=b."castleId"', array('castleId', 'x', 'y'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->where('capital = true')
            ->order('mapCastleId');

        $startPositions = array();

        foreach ($this->selectAll($select) as $position) {
            $startPositions[$position['castleId']] = $position;
        }

        return $startPositions;
    }

    public function add($x, $y)
    {
        $data = array(
            'mapId' => $this->mapId,
            'x' => $x,
            'y' => $y,
            'name' => 'a',
            'income' => 1,
            'defense' => 1
        );
        $this->insert($data);
    }

}

