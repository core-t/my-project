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
                    '"mapId" = 7',
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

    public function castles($mapId)
    {
        $castlesSchema = Application_Model_Board::getCastlesSchema();
        foreach ($castlesSchema as $castle) {
            if ($castle['capital']) {
                $c = 't';
            } else {
                $c = 'f';
            }
            $data = array(
                'mapId' => $mapId,
                'name' => $castle['name'],
                'income' => $castle['income'],
                'defense' => $castle['defensePoints'],
                'x' => $castle['position']['x'],
                'y' => $castle['position']['y'] + 8,
                'capital' => $c
            );
//            var_dump($data);exit;
            try {
                $this->_db->insert('mapcastles', $data);
            } catch (Exception $e) {
                echo $e;
                var_dump($data);
                exit;
            }
            $mapCastleId = $this->_db->lastSequenceId($sequence);


            foreach ($castle['production'] as $unit => $production) {
                $data = array(
                    "unitId" => $mUnit->getUnitIdByName($unit),
                    "time" => $production['time'],
                    "cost" => $production['cost'],
                    'mapCastleId' => $mapCastleId
                );
                $this->_db->insert('mapcastlesproduction', $data);

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
                'y' => $ruin['y'] + 8
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
                'y' => $ruin['y'] + 8
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

