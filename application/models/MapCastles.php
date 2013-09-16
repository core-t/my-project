<?php

class Application_Model_MapCastles extends Game_Db_Table_Abstract
{
    protected $_name = 'mapcastles';
    protected $_primary = 'mapCastleId';
    protected $_sequence = '';
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
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId);
        try {
            $castles = $this->_db->query($select)->fetchAll();
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }

        $mMapCastlesProduction = new Application_Model_MapCastlesProduction($this->_db);
        $mapCastles = array();

        foreach ($castles as $val) {
            $mapCastles[$val['mapCastleId']] = $val;
            $mapCastles[$val['mapCastleId']]['defensePoints'] = $val['defense'];
            $mapCastles[$val['mapCastleId']]['production'] = $mMapCastlesProduction->getCastleProduction($val['mapCastleId']);
            $mapCastles[$val['mapCastleId']]['position'] = array('x' => $val['x'], 'y' => $val['y']);
            $mapCastles[$val['mapCastleId']]['castleId'] = $val['mapCastleId'];
        }

        return $mapCastles;
    }

    public function getDefaultStartPositions()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('mapCastleId', 'x', 'y'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->where('capital = true')
            ->order('mapCastleId');

        return $this->selectAll($select);
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
