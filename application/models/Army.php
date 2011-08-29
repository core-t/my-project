<?php

class Application_Model_Army extends Game_Db_Table_Abstract {

    protected $_name = 'army';
    protected $_primary = 'armyId';
    protected $_db;
    protected $_gameId;

    public function __construct($gameId) {
        $this->_gameId = $gameId;
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function createArmy($position, $playerId, $sleep = 0) {
        $armyId = $this->getNewArmyId();
        $data = array(
            'armyId' => $armyId,
            'playerId' => $playerId,
            'gameId' => $this->_gameId,
            'position' => $position['x'] . ',' . $position['y']
        );
        try{
            $this->_db->insert($this->_name, $data);
            return $armyId;
        }catch(Exception $e) {
            if($sleep > 10){
                throw new Exception($e->getMessage());
            }
            sleep(rand(0,$sleep));
            $armyId = $this->createArmy($position, $playerId, $sleep + 1);
        }
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
                    ->where('"playerId" = ?', $playerId)
                    ->where('destroyed = false');
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

    public function getHeroIdByArmyIdPlayerId($armyId, $playerId) {
        try {
            $select = $this->_db->select()
                    ->from('hero', 'heroId')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" = ?', $playerId)
                    ->where('"' . $this->_primary . '" = ?', $armyId);
            $result = $this->_db->query($select)->fetchAll();
            if(isset($result[0]['heroId'])){
                return $result[0]['heroId'];
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    private function getArmyHeroes($armyId, $in = false) {
        try {
            $select = $this->_db->select()
                    ->from('hero', array('heroId', 'numberOfMoves', 'attackPoints', 'defensePoints', 'armyId', 'experience', 'movesLeft'))
                    ->where('"gameId" = ?', $this->_gameId)
                    ->order('attackPoints DESC', 'defensePoints DESC', 'numberOfMoves DESC');
            if($in) {
                $select->where('"' . $this->_primary . '" IN (?)', new Zend_Db_Expr($armyId));
            } else {
                $select->where('"' . $this->_primary . '" = ?', $armyId);
            }
            $result = $this->_db->query($select)->fetchAll();
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

    private function getArmySoldiers($armyId, $in = false) {
        try {
            $select = $this->_db->select()
                    ->from(array('a' => 'soldier'))
                    ->join(array('b' => 'unit'), 'a."unitId" = b."unitId"')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->order(array('canFly', 'attackPoints', 'defensePoints', 'numberOfMoves', 'a.unitId'));
            if($in) {
                $select->where('"' . $this->_primary . '" IN (?)', new Zend_Db_Expr($armyId));
            } else {
                $select->where('"' . $this->_primary . '" = ?', $armyId);
            }
            $result = $this->_db->query($select)->fetchAll();
            return $result;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getArmyByArmyIdPlayerId($armyId, $playerId) {
        try {
            $select = $this->_db->select()
                    ->from($this->_name)
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" = ?', $playerId)
                    ->where('destroyed = false')
                    ->where('"' . $this->_primary . '" = ?', $armyId);
            $result = $this->_db->query($select)->fetchAll();
            $result[0]['heroes'] = $this->getArmyHeroes($result[0]['armyId']);
            $result[0]['soldiers'] = $this->getArmySoldiers($result[0]['armyId']);
            $result[0]['movesLeft'] = $this->calculateArmyMovesLeft($result[0]['armyId']);
            return $result[0];
        } catch (PDOException $e) {
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
            if($result[0]['destroyed']){
                $result[0]['heroes'] = array();
                $result[0]['soldiers'] = array();
                return $result[0];
            }
            $result[0]['heroes'] = $this->getArmyHeroes($result[0]['armyId']);
            $result[0]['soldiers'] = $this->getArmySoldiers($result[0]['armyId']);
            if(empty($result[0]['heroes']) && empty($result[0]['soldiers'])){
                $result[0]['destroyed'] = true;
                $this->destroyArmy($result[0]['armyId'], $result[0]['playerId']);
                return $result[0];
            }else{
                $result[0]['movesLeft'] = $this->calculateArmyMovesLeft($result[0]['armyId']);
                return $result[0];
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getEnemyArmiesFieldsPositions($playerId) {
        $fields = Application_Model_Board::getBoardFields();
//         echo '<pre>';print_r($fields);echo '</pre>';
        try {
            $select = $this->_db->select()
                    ->from($this->_name, 'position')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" != ?', $playerId)
                    ->where('destroyed = false');
            $result = $this->_db->query($select)->fetchAll();
            foreach($result as $row){
                $position = explode(',', substr($row['position'], 1 , -1));
                $fields[$position[1]/40][$position[0]/40] = 'e';
            }
            return $fields;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getArmyPositionByArmyId($armyId, $playerId) {
        try {
            $select = $this->_db->select()
                    ->from($this->_name, 'position')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" = ?', $playerId)
                    ->where('destroyed = false')
                    ->where('"' . $this->_primary . '" = ?', $armyId);
            $result = $this->_db->query($select)->fetchAll();
            return $result[0];
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function calculateArmyMovesLeft($armyId) {
        $heroMovesLeft = $this->getMinHeroesMovesLeft($armyId);
        $soldierMovesLeft = $this->getMinSoldiersMovesLeft($armyId);
        if($soldierMovesLeft AND $heroMovesLeft) {
            if($heroMovesLeft > $soldierMovesLeft) {
                $movesLeft = $soldierMovesLeft;
            } else {
                $movesLeft = $heroMovesLeft;
            }
        } elseif($soldierMovesLeft) {
            $movesLeft = $soldierMovesLeft;
        } elseif($heroMovesLeft) {
            $movesLeft = $heroMovesLeft;
        } else {
            $movesLeft = 0;
        }
        return $movesLeft;
    }

    private function getMinHeroesMovesLeft($armyId) {
        try {
            $select = $this->_db->select()
                    ->from('hero', 'min("movesLeft")')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"' . $this->_primary . '" = ?', $armyId);
            $result = $this->_db->query($select)->fetchAll();
            return $result[0]['min'];
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    private function getMinSoldiersMovesLeft($armyId) {
        try {
            $select = $this->_db->select()
                    ->from('soldier', 'min("movesLeft")')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"' . $this->_primary . '" = ?', $armyId);
            $result = $this->_db->query($select)->fetchAll();
            return $result[0]['min'];
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function updateArmyPosition($armyId, $playerId, $data) {
        $data1 = array(
            'position' => $data['position']
        );
        try {
            $select = $this->_db->select()
                    ->from('hero', array('movesLeft', 'heroId'))
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" = ?', $playerId)
                    ->where('"' . $this->_primary . '" = ?', $armyId);
            $result = $this->_db->query($select)->fetchAll();
            foreach($result as $row) {
                $data2 = array(
                    'movesLeft' => $row['movesLeft'] - $data['movesSpend']
                );
                $where1 = $this->_db->quoteInto('"heroId" = ?', $row['heroId']);
                $this->_db->update('hero', $data2, $where1);
            }
            $select = $this->_db->select()
                    ->from('soldier', array('movesLeft', 'soldierId'))
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"' . $this->_primary . '" = ?', $armyId);
            $result = $this->_db->query($select)->fetchAll();
            foreach($result as $row) {
                $data2 = array(
                    'movesLeft' => $row['movesLeft'] - $data['movesSpend']
                );
                $where1 = $this->_db->quoteInto('"soldierId" = ?', $row['soldierId']);
                $this->_db->update('soldier', $data2, $where1);
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }

        try {
            $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $armyId);
            $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
            $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
            return $this->_db->update($this->_name, $data1, $where);
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

    public function destroySoldier($soldierId) {
        $where[] = $this->_db->quoteInto('"soldierId" = ?', $soldierId);
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        $this->_db->delete('soldier', $where);
    }

    public function armyRemoveHero($heroId) {
        $data = array(
            'armyId' => null
        );
        $where = $this->_db->quoteInto('"heroId" = ?', $heroId);
        $this->_db->update('hero', $data, $where);
    }

    public function destroyArmy($armyId, $playerId) {
        $data = array(
            'destroyed' => 'true'
        );
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $armyId);
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        return $this->_db->update($this->_name, $data, $where);
//         return $this->_db->delete($this->_name, $where);
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
                        ->where('ready = true')
                        ->where('"gameId" = ?', $this->_gameId);
                $result = $this->_db->query($select)->fetchAll();
                if (isset($result[0]['number'])) {
                    if ($result[0]['number'] == $number) {
                        return true;
                    }
                }
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function updateAllArmiesFromCastlePosition($position) {
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
                    ->where('destroyed = false')
                    ->where('position::varchar IN (?)', $points);
            $result = $this->_db->query($select)->fetchAll();
            foreach ($result as $k => $army) {
                $heroes = $this->getArmyHeroes($army['armyId']);
                $soldiers = $this->getArmySoldiers($army['armyId']);
                if (empty($heroes) AND empty($soldiers)) {
                    $this->destroyArmy($army['armyId'], $army['playerId']);
                    unset($result[$k]);
                } else {
                    $result[$k]['heroes'] = $heroes;
                    $result[$k]['soldiers'] = $soldiers;
                }
            }
            return $result;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getAllUnitsFromCastlePosition($position) {
        $points = array(
            '(' . $position['x'] . ',' . $position['y'] . ')',
            '(' . ($position['x'] + 40) . ',' . $position['y'] . ')',
            '(' . $position['x'] . ',' . ($position['y'] + 40) . ')',
            '(' . ($position['x'] + 40) . ',' . ($position['y'] + 40) . ')'
        );
        $ids = '';
        try {
            $select = $this->_db->select()
                    ->from($this->_name, $this->_primary)
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('destroyed = false')
                    ->where('position::varchar IN (?)', $points);
            $result = $this->_db->query($select)->fetchAll();
            foreach ($result as $id) {
                if($ids){
                    $ids .= ',';
                }
                $ids .= $id['armyId'];
            }
            if($ids){
                $heroes = $this->getArmyHeroes($ids, true);
                $soldiers = $this->getArmySoldiers($ids, true);
                return array('heroes' => $heroes, 'soldiers' => $soldiers);
            } else {
                return array('heroes' => array(), 'soldiers' => array());
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getAllUnitsFromPosition($position) {
        $position = '(' . $position['x'] . ',' . $position['y'] . ')';
        $ids = '';
        try {
            $select = $this->_db->select()
                    ->from($this->_name)
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('destroyed = false')
                    ->where('position::varchar = (?)', $position);
            $result = $this->_db->query($select)->fetchAll();
            foreach ($result as $id) {
                if($ids){
                    $ids .= ',';
                }
                $ids .= $id['armyId'];
            }
            if($ids){
                $heroes = $this->getArmyHeroes($ids, true);
                $soldiers = $this->getArmySoldiers($ids, true);
                return array('heroes' => $heroes, 'soldiers' => $soldiers);
            } else {
                return array('heroes' => null, 'soldiers' => null);
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function updateAllArmiesFromPosition($position) {
        $position = '(' . $position['x'] . ',' . $position['y'] . ')';
        try {
            $select = $this->_db->select()
                    ->from($this->_name)
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('destroyed = false')
                    ->where('position::varchar = (?)', $position);
            $result = $this->_db->query($select)->fetchAll();
            foreach ($result as $k => $army) {
                $heroes = $this->getArmyHeroes($army['armyId']);
                $soldiers = $this->getArmySoldiers($army['armyId']);
                if (empty($heroes) AND empty($soldiers)) {
                    $this->destroyArmy($army['armyId'], $army['playerId']);
                    unset($result[$k]);
                } else {
                    $result[$k]['heroes'] = $heroes;
                    $result[$k]['soldiers'] = $soldiers;
                }
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
                    ->where('destroyed = false')
                    ->where('position::varchar = (?)', $position);
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0]['armyId'])) {
                return $result[0]['armyId'];
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function doProduction($playerId, $castles) {
        foreach ($castles as $castleId => $castle) {
            $armyId = $this->getArmyIdFromPosition($castle['position']);
            if (!$armyId) {
                $armyId = $this->createArmy($castle['position'], $playerId);
            }
            if (!empty($armyId)) {
                try {
                    $select = $this->_db->select()
                            ->from('castle', 'production')
                            ->where('"gameId" = ?', $this->_gameId)
                            ->where('"castleId" = ?', $castleId)
                            ->where('"playerId" = ?', $playerId);
                    $result = $this->_db->query($select)->fetchAll();
                    if (isset($result[0]['production'])) {

                    }
                    $this->addSoldierToArmy($armyId, $unitId, $playerId);
                } catch (PDOException $e) {
                    throw new Exception($select->__toString());
                }
            }
        }
    }

    public function addSoldierToArmy($armyId, $unitId, $playerId) {
        $select1 = $this->_db->select()
                    ->from('unit', 'numberOfMoves')
                    ->where('"unitId" = ?', $unitId);
        $data = array(
            'armyId' => $armyId,
            'gameId' => $this->_gameId,
            'unitId' => $unitId,
            'movesLeft' => new Zend_Db_Expr('('.$select1->__toString().')')
        );
        return $this->_db->insert('soldier', $data);
    }

    public function addHeroToArmy($armyId, $heroId) {
        $data = array(
            'armyId' => $armyId,
            'gameId' => $this->_gameId,
            'movesLeft' => new Zend_Db_Expr('"numberOfMoves"')
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
                    ->where('destroyed = false')
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

    private function heroUpdateArmyId($heroId, $newArmyId) {
        $data = array(
            $this->_primary => $newArmyId
        );
        $where[] = $this->_db->quoteInto('"heroId" = ?', $heroId);
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        return $this->_db->update('hero', $data, $where);
    }

    private function soldierUpdateArmyId($soldierId, $newArmyId) {
        $data = array(
            $this->_primary => $newArmyId
        );
        $where[] = $this->_db->quoteInto('"soldierId" = ?', $soldierId);
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        return $this->_db->update('soldier', $data, $where);
    }

    public function resetHeroesMovesLeft($playerId) {
        $data = array(
            'movesLeft' => new Zend_Db_Expr('"numberOfMoves"')
        );
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        return $this->_db->update('hero', $data, $where);
    }

    public function resetSoldiersMovesLeft($playerId) {
        $select1 = $this->_db->select()
                    ->from('unit', 'numberOfMoves')
                    ->where('soldier."unitId" = unit."unitId"');
        $data = array(
            'movesLeft' => new Zend_Db_Expr('('.$select1->__toString().')')
        );
        $select2 = $this->_db->select()
                    ->from('army', 'armyId')
                    ->where('"playerId" = ?', $playerId)
                    ->where('destroyed = false')
                    ->where('"gameId" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"armyId" IN (?)', $select2);
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        return $this->_db->update('soldier', $data, $where);
    }

    public function setHeroesMovesLeft($playerId, $movesLeft) {
        $data = array(
            'movesLeft' => new Zend_Db_Expr('"numberOfMoves"')
        );
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        return $this->_db->update('hero', $data, $where);
    }

    public function setSoldiersMovesLeft($playerId) {
        $select1 = $this->_db->select()
                    ->from('unit', 'numberOfMoves')
                    ->where('soldier."unitId" = unit."unitId"');
        $data = array(
            'movesLeft' => new Zend_Db_Expr('('.$select1->__toString().')')
        );
        $select2 = $this->_db->select()
                    ->from('army', 'armyId')
                    ->where('"playerId" = ?', $playerId)
                    ->where('destroyed = false')
                    ->where('"gameId" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"armyId" IN (?)', $select2);
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        return $this->_db->update('soldier', $data, $where);
    }

    public function splitArmy($h, $s, $parentArmyId, $playerId){
        $position = $this->getArmyPositionByArmyId($parentArmyId, $playerId);
        $position = explode(',', substr($position['position'], 1 , -1));
        $heroesIds = explode(',', $h);
        $soldiersIds = explode(',', $s);
//         throw new Exception(Zend_Debug::dump($soldiersIds,null,false));
        if((isset($heroesIds[0]) && !empty($heroesIds[0])) || (isset($soldiersIds) && !empty($soldiersIds))){
            $newArmyId = $this->createArmy(array('x' => $position[0], 'y' => $position[1]), $playerId);
            foreach($heroesIds as $heroId){
                if(!empty($heroId)){
                    $this->heroUpdateArmyId($heroId, $newArmyId);
                }
            }
            foreach($soldiersIds as $soldierId){
                if(!empty($soldierId)){
                    $this->soldierUpdateArmyId($soldierId, $newArmyId);
                }
            }
            return $newArmyId;
        }
    }

    public function playerArmiesExists($playerId){
        try {
            $select = $this->_db->select()
                    ->from($this->_name, 'armyId')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('destroyed = false')
                    ->where('"playerId" = ?', $playerId);
            $result = $this->_db->query($select)->fetchAll();
            if (count($result)) {
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function zeroHeroMovesLeft($armyId, $heroId, $playerId) {
        $data = array(
            'movesLeft' => 0
        );
        $where[] = $this->_db->quoteInto('"armyId" = ?', $armyId);
        $where[] = $this->_db->quoteInto('"heroId" = ?', $heroId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        return $this->_db->update('hero', $data, $where);
    }
    
    public function isHeroInGame($playerId){
        try {
            $select = $this->_db->select()
                    ->from('hero', 'heroId')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" = ?', $playerId);
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0]['heroId'])) {
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function connectHero($playerId) {
        $data = array(
            'armyId' => null,
            'gameId' => $this->_gameId
        );
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        return $this->_db->update('hero', $data, $where);
    }
    
    public function getDeadHeroId($playerId){
        try {
            $select = $this->_db->select()
                    ->from('hero', array('heroId','armyId'))
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" = ?', $playerId);
            $result = $this->_db->query($select)->fetchAll();
            if (!isset($result[0]['armyId'])) {
                return $result[0]['heroId'];
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function heroResurection($heroId, $position, $playerId){
        $armyId = $this->getArmyIdFromPosition($position);
        if(!$armyId){
            $armyId = $this->createArmy($position, $playerId);
        }
        $this->addHeroToArmy($armyId, $heroId);
        $this->zeroHeroMovesLeft($armyId, $heroId, $playerId);
        return $armyId;
    }
}

