<?php

class Application_Model_Army extends Warlords_Db_Table_Abstract {

    protected $_name = 'army';
    protected $_primary = 'armyId';
    protected $_sequence = "army_armyId_seq";
    protected $_db;
    protected $_gameId;

    public function __construct($gameId) {
        $this->_gameId = $gameId;
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function createArmy($position, $numberOfMoves, $playerId) {
        $armyId = $this->getNewArmyId();
        $data = array(
            'armyId' => $armyId,
            'playerId' => $playerId,
            'gameId' => $this->_gameId,
            'position' => $position['x'] . ',' . $position['y'],
            'movesLeft' => $numberOfMoves
        );
        $this->_db->insert($this->_name, $data);
        return $armyId;
    }

    private function getNewArmyId() {
        try {
            $select = $this->_db->select()
                    ->from($this->_name, 'max("armyId")')
                    ->where('"gameId" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            return $result[0]['max'] + 1;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getPlayerArmies($playerId) {
        try {
            $select = $this->_db->select()
                    ->from($this->_name)
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" = ?', $playerId);
            $result = $this->_db->query($select)->fetchAll();
            $array = array();
            foreach ($result as $k => $army) {
                $array['army' . $army['armyId']] = $army;
                $array['army' . $army['armyId']]['heroes'] = $this->getArmyHeroes($army['armyId']);
                $array['army' . $army['armyId']]['soldiers'] = $this->getArmySoldiers($army['armyId']);
                if (empty($array['army' . $army['armyId']]['heroes']) AND empty($array['army' . $army['armyId']]['soldiers'])) {
                    $this->destroyArmy($array['army' . $army['armyId']]['armyId'], $playerId);
                    unset($array['army' . $army['armyId']]);
                }
            }
            return $array;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    private function getArmyHeroes($armyId) {
        try {
            $select = $this->_db->select()
                    ->from('hero', array('heroId', 'numberOfMoves', 'attackPoints', 'defensePoints', 'armyId', 'experience'))
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"' . $this->_primary . '" = ?', $armyId)
                    ->order('attackPoints DESC');
            $result = $this->_db->query($select)->fetchAll();
            $hero = array();
            foreach ($result as $k => $row) {
                $result[$k]['artefacts'] = $this->getArtefactsByHeroId($row['heroId']);
            }
            return $result;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    private function getArtefactsByHeroId($heroId) {
        try {
            $select = $this->_db->select()
                    ->from(array('a' => 'inventory'))
                    ->join(array('b' => 'artefact'), 'a."artefactId" = b."artefactId"')
                    ->where('"heroId" = ?', $heroId);
            return $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    private function getArmySoldiers($armyId) {
        try {
            $select = $this->_db->select()
                    ->from(array('a' => 'soldier'))
                    ->join(array('b' => 'unit'), 'a."unitId" = b."unitId"')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"' . $this->_primary . '" = ?', $armyId)
                    ->order('attackPoints DESC');
            $result = $this->_db->query($select)->fetchAll();
            return $result;
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getArmyByArmyIdPlayerId($armyId, $playerId) {
        try {
            $select = $this->_db->select()
                    ->from($this->_name)
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" = ?', $playerId)
                    ->where('"' . $this->_primary . '" = ?', $armyId);
            $result = $this->_db->query($select)->fetchAll();
            foreach ($result as $k => $army) {
                $result[$k]['heroes'] = $this->getArmyHeroes($army['armyId']);
                $result[$k]['soldiers'] = $this->getArmySoldiers($army['armyId']);
            }
            return $result[0];
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getArmyById($armyId) {
        try {
            $select = $this->_db->select()
                    ->from($this->_name)
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"' . $this->_primary . '" = ?', $armyId);
            $result = $this->_db->query($select)->fetchAll();
            $result[0]['heroes'] = $this->getArmyHeroes($result[0]['armyId']);
            $result[0]['soldiers'] = $this->getArmySoldiers($result[0]['armyId']);
            return $result[0];
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getArmyPositionByArmyId($armyId, $playerId) {
        try {
            $columns = array(
                'position',
                'movesLeft'
            );
            $select = $this->_db->select()
                    ->from($this->_name, $columns)
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" = ?', $playerId)
                    ->where('"' . $this->_primary . '" = ?', $armyId);
            $result = $this->_db->query($select)->fetchAll();
            return $result[0];
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }
    }

    public function updateArmyPosition($armyId, $playerId, $data) {
        try {
            $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $armyId);
            $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
            $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
            return $this->_db->update($this->_name, $data, $where);
        } catch (PDOException $e) {
            print_r($e);
//            $dbProfiler = $this->_db->getProfiler();
//            $dbQuery = $dbProfiler->getLastQueryProfile();
//            $dbSQL = $dbQuery->getQuery();
//            print_r($dbSQL);
        }
    }

    public function updateArmyFull($armyOld, $armyNew) {
        foreach ($armyOld['soldiers'] as $unitOld) {
            $delete = true;
            foreach ($armyNew['soldiers'] as $k => $unitNew) {
                if ($unitOld['soldierId'] == $unitNew['soldierId']) {
                    $delete = false;
                    unset($armyNew['soldiers'][$k]);
                }
            }
            if ($delete) {
                $this->destroySoldier($unitOld['soldierId']);
            }
        }
        foreach ($armyOld['heroes'] as $unitOld) {
            $delete = true;
            foreach ($armyNew['heroes'] as $k => $unitNew) {
                if ($unitOld['heroId'] == $unitNew['heroId']) {
                    $delete = false;
                    unset($armyNew['heroes'][$k]);
                }
            }
            if ($delete) {
                $this->destroyHero($unitOld['heroId']);
            }
        }
    }

    private function destroySoldier($soldierId) {
        $where = $this->_db->quoteInto('"soldierId" = ?', $soldierId);
        $this->_db->delete('soldier', $where);
    }

    private function destroyHero($heroId) {
        $where = $this->_db->quoteInto('"heroId" = ?', $heroId);
        $this->_db->delete('hero', $where);
    }

    public function destroyArmy($armyId, $playerId) {
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $armyId);
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        return $this->_db->delete($this->_name, $where);
    }

    public function allArmiesReady() {
        try {
            $select = $this->_db->select()
                    ->from($this->_name, 'count(*) as number')
                    ->where('"gameId" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0]['number'])) {
                $number = $result[0]['number'];
                $select = $this->_db->select()
                        ->from('playersingame', 'count(*) as number')
                        ->where('"gameId" = ?', $this->_gameId);
                $result = $this->_db->query($select)->fetchAll();
                if (isset($result[0]['number'])) {
                    if ($result[0]['number'] == $number) {
                        return true;
                    }
                }
            }
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getAllArmiesFromCastlePosition($position) {
        $points = array(
            '(' . $position['x'] . ',' . $position['y'] . ')',
            '(' . ($position['x'] + 40) . ',' . $position['y'] . ')',
            '(' . $position['x'] . ',' . ($position['y'] + 40) . ')',
            '(' . ($position['x'] + 40) . ',' . ($position['y'] + 40) . ')'
        );
        try {
            $select = $this->_db->select()
                    ->from($this->_name)
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('position::varchar IN (?)', $points);
            $result = $this->_db->query($select)->fetchAll();
            foreach ($result as $k => $army) {
                $result[$k]['heroes'] = $this->getArmyHeroes($army['armyId']);
                $result[$k]['soldiers'] = $this->getArmySoldiers($army['armyId']);
            }
            return $result;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getAllArmiesFromPosition($position) {
        $position = '(' . $position['x'] . ',' . $position['y'] . ')';
        try {
            $select = $this->_db->select()
                    ->from($this->_name)
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('position::varchar = (?)', $position);
            $result = $this->_db->query($select)->fetchAll();
            foreach ($result as $k => $army) {
                $result[$k]['heroes'] = $this->getArmyHeroes($army['armyId']);
                $result[$k]['soldiers'] = $this->getArmySoldiers($army['armyId']);
            }
            return $result;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getArmyIdFromPosition($position) {
        $position = '(' . $position['x'] . ',' . $position['y'] . ')';
        try {
            $select = $this->_db->select()
                    ->from($this->_name, 'armyId')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('position::varchar = (?)', $position);
            $result = $this->_db->query($select)->fetchAll();
            if(isset($result[0]['armyId'])) {
                return $result[0]['armyId'];
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function doProduction($playerId, $castles) {
        foreach ($castles as $castle) {
            $armyId = $this->getArmyIdFromPosition($castle['position']);
            if(!$armyId) {
                $armyId = $this->createArmy($castle['position'], 10, $playerId);
            }
            if (!empty($armyId)) {
                $this->addSoldierToArmy($armyId);
            }
        }
    }

    public function addSoldierToArmy($armyId) {
        $data = array(
            'armyId' => $armyId,
            'gameId' => $this->_gameId,
            'unitId' => 1
        );
        $this->_db->insert('soldier', $data);
    }

    public function addHeroToArmy($armyId, $heroId) {
        $data = array(
            'armyId' => $armyId,
            'gameId' => $this->_gameId
        );
        $where = $this->_db->quoteInto('"heroId" = ?', $heroId);
        return $this->_db->update('hero', $data, $where);
    }

    public function joinArmiesAtPosition($position, $playerId) {
        $position = '(' . $position . ')';
        try {
            $select = $this->_db->select()
                    ->from($this->_name, 'armyId')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" = ?', $playerId)
                    ->where('position::varchar = (?)', $position);
            $result = $this->_db->query($select)->fetchAll();
            if (count($result) == 1) {
                return $result[0]['armyId'];
            }
            foreach ($result as $army) {
                $this->heroesUpdateArmyId($army['armyId'], $result[0]['armyId'], $playerId);
                $this->soldiersUpdateArmyId($army['armyId'], $result[0]['armyId']);
            }
            return $result[0]['armyId'];
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    private function heroesUpdateArmyId($oldArmyId, $newArmyId, $playerId) {
        $data = array(
            $this->_primary => $newArmyId
        );
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $oldArmyId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        return $this->_db->update('hero', $data, $where);
    }

    private function soldiersUpdateArmyId($oldArmyId, $newArmyId) {
        $data = array(
            $this->_primary => $newArmyId
        );
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $oldArmyId);
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        return $this->_db->update('soldier', $data, $where);
    }

}

