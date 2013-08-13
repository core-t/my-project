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
            ->where('capital = true');
        try {
            $castles = $this->_db->query($select)->fetchAll();
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }

        return array(
            'white' => array(
                'id' => $castles[0]['mapCastleId'],
                'position' => array('x' => $castles[0]['x'], 'y' => $castles[0]['y'])
            ),
            'green' => array(
                'id' => $castles[1]['mapCastleId'],
                'position' => array('x' => $castles[1]['x'], 'y' => $castles[1]['y'])
            ),
            'red' => array(
                'id' => $castles[2]['mapCastleId'],
                'position' => array('x' => $castles[2]['x'], 'y' => $castles[2]['y'])
            ),
            'yellow' => array(
                'id' => $castles[3]['mapCastleId'],
                'position' => array('x' => $castles[3]['x'], 'y' => $castles[3]['y'])
            )
        );
    }
}

