<?php

class Admin_Model_Map extends Coret_Model_ParentDb
{
    protected $_name = 'map';
    protected $_primary = 'mapId';
    protected $_columns = array(
        'mapId' => array('label' => 'Map ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'name' => array('label' => 'Nazwa', 'type' => 'varchar'),
        'mapWidth' => array('label' => 'Szerokość', 'type' => 'number'),
        'mapHeight' => array('label' => 'Wysokość', 'type' => 'number'),
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
            unset($unit['mapUnitId']);
            $unit['mapId'] = $mapId;

            try {
                $this->_db->insert('mapunits', $unit);
            } catch (Exception $e) {
                var_dump($unit);
                throw $e;
            }
        }

    }

    public function castles($mapId)
    {
        $mMapCastle = new Application_Model_MapCastles(7);
        $mMapCastlesProduction = new Application_Model_MapCastlesProduction();

        $castlesSchema = $mMapCastle->getMapCastles();

        foreach ($castlesSchema as $castle) {
            $production = $mMapCastlesProduction->getCastleProduction($castle['mapCastleId']);

            $castle['mapId'] = $mapId;
            unset($castle['mapCastleId']);

            try {
                $this->_db->insert('mapcastles', $castle);
            } catch (Exception $e) {
                echo $e;
                var_dump($data);
                exit;
            }

            $mapCastleId = $this->_db->lastSequenceId('mapcastles_mapCastleId_seq');

            foreach ($production as $unit) {
                $unit['mapCastleId'] = $mapCastleId;
                try {
                    $this->_db->insert('mapcastlesproduction', $unit);
                } catch (Exception $e) {
                    echo $e;
                    var_dump($unit);
                    exit;
                }
            }
        }
    }

    public function ruins($mapId)
    {
        $ruinsSchema = Application_Model_Board::getRuins();
        foreach ($ruinsSchema as $ruin) {
            $data = array(
                'mapId' => $mapId,
                'x' => $ruin['x'],
                'y' => $ruin['y'] + 80
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
        $towersSchema = Application_Model_Board::getTowers();
        foreach ($towersSchema as $ruin) {
            $data = array(
                'mapId' => $mapId,
                'x' => $ruin['x'],
                'y' => $ruin['y'] + 80
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

