<?php

class Admin_Model_Map extends Coret_Model_ParentDb
{
    protected $_name = 'map';
    protected $_primary = 'mapId';
    protected $_sequence = 'map_mapId_seq';

    protected $_columns = array(
        'mapId' => array('label' => 'Map ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'name' => array('label' => 'Nazwa', 'type' => 'varchar'),
        'mapWidth' => array('label' => 'Szerokość', 'type' => 'number'),
        'mapHeight' => array('label' => 'Wysokość', 'type' => 'number'),
        'maxPlayers' => array('label' => 'Maksymalna ilość graczy', 'type' => 'number'),
        'date' => array('label' => 'Data', 'type' => 'date', 'active' => array('db' => false, 'form' => false)),
    );
    protected $_adminId = 'playerId';

    public function fields($mapId)
    {
        $mapFields4 = new Application_Model_MapFields(4);
        $fields = $mapFields4->getMapFields();
        foreach ($fields as $y => $row) {
            foreach ($row as $x => $type) {
                $data = array(
                    'type' => $type
                );
                $where = array(
                    $this->_db->quoteInto('"mapId" = ?', $mapId),
                    $this->_db->quoteInto('"x" = ?', $x),
                    $this->_db->quoteInto('"y" = ?', $y + 8)
                );
                try {
                    $this->_db->update('mapfields', $data, $where);
                } catch (Exception $e) {
                    echo $e;
                    exit;
                }
            }
        }
    }

    public function units($mapId)
    {
        $mMapUnits = new Application_Model_MapUnits(7);
        $units = $mMapUnits->getUnits();

        foreach ($units as $unit) {
            if (empty($unit)) {
                continue;
            }
            unset($unit['mapUnitId']);
            $unit['mapId'] = $mapId;
            if ($unit['canFly']) {
                $unit['canFly'] = 't';
            } else {
                $unit['canFly'] = 'f';
            }
            if ($unit['canSwim']) {
                $unit['canSwim'] = 't';
            } else {
                $unit['canSwim'] = 'f';
            }

            try {
                $this->_db->insert('mapunits', $unit);
            } catch (Exception $e) {
                throw $e;
            }
        }

    }

    public function castles($mapId)
    {
        $mMapCastle = new Application_Model_MapCastles(7);

        $castlesSchema = $mMapCastle->getMapCastles();

        foreach ($castlesSchema as $castle) {
            $data = array(
                'mapId' => $mapId,
                'x' => $castle['x'],
                'y' => $castle['y'] + 79,
                'name' => $castle['name'],
                'income' => $castle['income'],
                'defense' => $castle['defense'],
            );

            if ($castle['capital']) {
                $data['capital'] = 't';
            } else {
                $data['capital'] = 'f';
            }

            try {
                $this->_db->insert('mapcastles', $data);
            } catch (Exception $e) {
                throw $e;
            }

            $mapCastleId = $this->_db->lastSequenceId('mapcastles_mapCastleId_seq');

            foreach ($castle['production'] as $unit) {
                $unit['mapCastleId'] = $mapCastleId;
                unset($unit['mapCastleProductionId']);
                try {
                    $this->_db->insert('mapcastlesproduction', $unit);
                } catch (Exception $e) {
                    var_dump($unit);
                    throw $e;
                }
            }
        }
    }

    public function ruins($mapId)
    {
        $mMapRuins = new Application_Model_MapRuins(7);
        $ruins = $mMapRuins->getMapRuins();

        foreach ($ruins as $ruin) {
            $data = array(
                'mapId' => $mapId,
                'x' => $ruin['x'],
                'y' => $ruin['y'] + 79
            );
            try {
                $this->_db->insert('mapruins', $data);
            } catch (Exception $e) {
                echo $e;
                var_dump($data);
                exit;
            }
        }
    }

    public function towers($mapId)
    {
        $mMapTowers = new Application_Model_MapTowers(7);
        $towers = $mMapTowers->getMapTowers();
        foreach ($towers as $tower) {
            $data = array(
                'mapId' => $mapId,
                'x' => $tower['x'],
                'y' => $tower['y'] + 79
            );
            try {
                $this->_db->insert('maptowers', $data);
            } catch (Exception $e) {
                echo $e;
                var_dump($data);
                exit;
            }
        }
    }
}

