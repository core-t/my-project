<?php

class Application_Model_Database extends Zend_Db_Table_Abstract {

    static public function getDb() {
        return new Zend_Db_Adapter_Pdo_Pgsql(array(
                    'host' => Zend_Registry::get('config')->resources->db->params->host,
                    'username' => Zend_Registry::get('config')->resources->db->params->username,
                    'password' => Zend_Registry::get('config')->resources->db->params->password,
                    'dbname' => Zend_Registry::get('config')->resources->db->params->dbname
                ));
    }

    static public function isPlayerCastle($gameId, $castleId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }

        $select = $db->select()
                ->from('castle', 'castleId')
                ->where('razed = false')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" = ?', $playerId)
                ->where('"castleId" = ?', $castleId);
        try {
            $castleId = $db->fetchOne($select);
            if ($castleId !== null) {
                return true;
            }
        } catch (Exception $e) {
            echo($select->__toString());
        }
    }

    static public function isCastleRazed($gameId, $castleId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $select = $db->select()
                ->from('castle', 'razed')
                ->where('"gameId" = ?', $gameId)
                ->where('"castleId" = ?', $castleId)
                ->where('razed = true');
        try {
            $razed = $db->fetchOne($select);
            if ($razed) {
                return true;
            }
        } catch (Exception $e) {
            echo($select->__toString());
        }
    }

    static public function joinArmiesAtPosition($gameId, $position, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $select = $db->select()
                ->from('army', 'armyId')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" = ?', $playerId)
                ->where('destroyed = false')
                ->where('x = ?', $position['x'])
                ->where('y = ?', $position['y']);
        try {
            $result = $db->query($select)->fetchAll();
            if (count($result) == 1) {// jeśli jest tylko jedna armia na pozycji
                return $result[0]['armyId'];
            }
            foreach ($result as $army)
            {
                self::heroesUpdateArmyId($gameId, $army['armyId'], $result[0]['armyId']);
                self::soldiersUpdateArmyId($gameId, $army['armyId'], $result[0]['armyId']);
            }
            if (isset($result[0]['armyId'])) {
                return $result[0]['armyId'];
            }
        } catch (Exception $e) {
            echo($select->__toString());
        }
    }

    static private function heroesUpdateArmyId($gameId, $oldArmyId, $newArmyId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $data = array(
            'armyId' => $newArmyId
        );
        $where = array(
            $db->quoteInto('"armyId" = ?', $oldArmyId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            return $db->update('heroesingame', $data, $where);
        } catch (Exception $e) {
            echo $e;
        }
    }

    static private function soldiersUpdateArmyId($gameId, $oldArmyId, $newArmyId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $data = array(
            'armyId' => $newArmyId
        );
        $where = array(
            $db->quoteInto('"armyId" = ?', $oldArmyId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            return $db->update('soldier', $data, $where);
        } catch (Exception $e) {
            echo $e;
        }
    }

    static public function updateArmyPosition($gameId, $armyId, $playerId, $data, $db = null) {
        $data1 = array(
            'x' => $data['x'],
            'y' => $data['y'],
        );
        if (!$db) {
            $db = self::getDb();
        }

        $select1 = $db->select()
                ->from('heroesingame', array('movesLeft', 'heroId'))
                ->where('"gameId" = ?', $gameId)
                ->where('"armyId" = ?', $armyId);
        try {
            $result1 = $db->query($select1)->fetchAll();
        } catch (Exception $e) {
            echo($select1->__toString());
            return;
        }

        foreach ($result1 as $row)
        {
            $data2 = array(
                'movesLeft' => $row['movesLeft'] - $data['movesSpend']
            );
            $where1 = array(
                $db->quoteInto('"heroId" = ?', $row['heroId']),
                $db->quoteInto('"gameId" = ?', $gameId)
            );
            try {
                $db->update('heroesingame', $data2, $where1);
            } catch (Exception $e) {
                echo($e);
            }
        }

        $select2 = $db->select()
                ->from('soldier', array('movesLeft', 'soldierId'))
                ->where('"gameId" = ?', $gameId)
                ->where('"armyId" = ?', $armyId);
        try {
            $result2 = $db->query($select2)->fetchAll();
        } catch (Exception $e) {
            echo($select2->__toString());
            return;
        }

        foreach ($result2 as $row)
        {
            $data2 = array(
                'movesLeft' => $row['movesLeft'] - $data['movesSpend']
            );
            $where1 = $db->quoteInto('"soldierId" = ?', $row['soldierId']);

            try {
                $db->update('soldier', $data2, $where1);
            } catch (Exception $e) {
                echo($e);
            }
        }

        $where = array(
            $db->quoteInto('"armyId" = ?', $armyId),
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId)
        );
        try {
            return $db->update('army', $data1, $where);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function getEnemyArmiesFieldsPositions($gameId, $playerId, $db = null) {
        $fields = Application_Model_Board::getBoardFields();
        if (!$db) {
            $db = self::getDb();
        }

        $select = $db->select()
                ->from('army', array('x', 'y'))
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" != ?', $playerId)
                ->where('destroyed = false');

        try {
            $result = $db->query($select)->fetchAll();
            foreach ($result as $row)
            {
                $fields[$row['y']][$row['x']] = 'e';
            }
            return $fields;
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getArmyByArmyIdPlayerId($gameId, $armyId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }

        $select = $db->select()
                ->from('army')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" = ?', $playerId)
                ->where('destroyed = false')
                ->where('"armyId" = ?', $armyId);
        try {
            $result = $db->fetchRow($select);
            if (isset($result['armyId'])) {
                $result['heroes'] = self::getArmyHeroes($gameId, $armyId, $db);
                $result['soldiers'] = self::getArmySoldiers($gameId, $armyId, $db);
                $result['movesLeft'] = self::calculateArmyMovesLeft($gameId, $armyId, $db);
                return $result;
            }
        } catch (Exception $e) {
            echo($e);
            echo $select->__toString();
        }
    }

    static private function getArmyHeroes($gameId, $armyId, $in = false, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }

        $select = $db->select()
                ->from(array('a' => 'hero'), array('heroId', 'numberOfMoves', 'attackPoints', 'defensePoints'))
                ->join(array('b' => 'heroesingame'), 'a."heroId" = b."heroId"', array('movesLeft'))
                ->where('"gameId" = ?', $gameId)
                ->order('attackPoints DESC', 'defensePoints DESC', 'numberOfMoves DESC');
        if ($in) {
            $select->where('"armyId" IN (?)', new Zend_Db_Expr($armyId));
        } else {
            $select->where('"armyId" = ?', $armyId);
        }
        try {
            $result = $db->query($select)->fetchAll();
            foreach ($result as $k => $row)
            {
                $result[$k]['artefacts'] = self::getArtefactsByHeroId($gameId, $row['heroId'], $db);
            }
            return $result;
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static private function getArtefactsByHeroId($gameId, $heroId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }

        $select = $db->select()
                ->from(array('a' => 'inventory'))
                ->join(array('b' => 'artefact'), 'a."artefactId" = b."artefactId"')
                ->where('"heroId" = ?', $heroId)
                ->where('"gameId" = ?', $gameId);
        try {
            return $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static private function getArmySoldiers($gameId, $armyId, $in = false, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }

        $select = $db->select()
                ->from(array('a' => 'soldier'))
                ->join(array('b' => 'unit'), 'a."unitId" = b."unitId"')
                ->where('"gameId" = ?', $gameId)
                ->order(array('canFly', 'attackPoints', 'defensePoints', 'numberOfMoves', 'a.unitId'));
        if ($in) {
            $select->where('"armyId" IN (?)', new Zend_Db_Expr($armyId));
        } else {
            $select->where('"armyId" = ?', $armyId);
        }
        try {
            $result = $db->query($select)->fetchAll();
            return $result;
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function calculateArmyMovesLeft($gameId, $armyId, $db = null) {
        $heroMovesLeft = self::getMinHeroesMovesLeft($gameId, $armyId, $db);
        $soldierMovesLeft = self::getMinSoldiersMovesLeft($gameId, $armyId, $db);
        if ($soldierMovesLeft AND $heroMovesLeft) {
            if ($heroMovesLeft > $soldierMovesLeft) {
                $movesLeft = $soldierMovesLeft;
            } else {
                $movesLeft = $heroMovesLeft;
            }
        } elseif ($soldierMovesLeft) {
            $movesLeft = $soldierMovesLeft;
        } elseif ($heroMovesLeft) {
            $movesLeft = $heroMovesLeft;
        } else {
            $movesLeft = 0;
        }
        return $movesLeft;
    }

    static private function getMinHeroesMovesLeft($gameId, $armyId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }

        $select = $db->select()
                ->from('heroesingame', 'min("movesLeft")')
                ->where('"gameId" = ?', $gameId)
                ->where('"armyId" = ?', $armyId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static private function getMinSoldiersMovesLeft($gameId, $armyId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }

        $select = $db->select()
                ->from('soldier', 'min("movesLeft")')
                ->where('"gameId" = ?', $gameId)
                ->where('"armyId" = ?', $armyId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getInGameWSSUIdsExceptMine($gameId, $playerId, $db = null) {

        if (!$db) {
            $db = self::getDb();
        }

        $select = $db->select()
                ->from('playersingame', 'webSocketServerUserId')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" != ?', $playerId);

        try {
            return $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getInGameWSSUIds($gameId, $db = null) {

        if (!$db) {
            $db = self::getDb();
        }

        $select = $db->select()
                ->from('playersingame', 'webSocketServerUserId')
                ->where('"gameId" = ?', $gameId);

        try {
            return $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function isPlayerTurn($gameId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }

        $select = $db->select()
                ->from('game', array('turnPlayerId'))
                ->where('"turnPlayerId" = ?', $playerId)
                ->where('"gameId" = ?', $gameId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($select->__toString());
            echo($e);
        }
    }

    static public function getPlayerIdByColor($gameId, $color, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }

        $select = $db->select()
                ->from('playersingame', 'playerId')
                ->where('"gameId" = ?', $gameId)
                ->where('color = ?', $color);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo $e;
            echo($select->__toString());
        }
    }

    static public function getPlayerArmies($gameId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }

        $select = $db->select()
                ->from('army')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" = ?', $playerId)
                ->where('destroyed = false');
        try {
            $result = $db->query($select)->fetchAll();
            $array = array();
            foreach ($result as $army)
            {
                $array['army' . $army['armyId']] = $army;
                $array['army' . $army['armyId']]['heroes'] = self::getArmyHeroes($gameId, $army['armyId'], false, $db);
                $array['army' . $army['armyId']]['soldiers'] = self::getArmySoldiers($gameId, $army['armyId'], false, $db);
                if (empty($array['army' . $army['armyId']]['heroes']) AND empty($array['army' . $army['armyId']]['soldiers'])) {
                    self::destroyArmy($gameId, $array['army' . $army['armyId']]['armyId'], $playerId, $db);
                    unset($array['army' . $army['armyId']]);
                }
            }
            return $array;
        } catch (Exception $e) {
            echo $e;
            echo($select->__toString());
        }
    }

    static public function getArmyById($gameId, $armyId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $select = $db->select()
                ->from('army')
                ->where('"gameId" = ?', $gameId)
                ->where('"armyId" = ?', $armyId);
        try {
            $result = $db->fetchRow($select);
            if ($result['destroyed']) {
                $result['heroes'] = array();
                $result['soldiers'] = array();
                return $result;
            }
            $result['heroes'] = self::getArmyHeroes($gameId, $result['armyId'], false, $db);
            $result['soldiers'] = self::getArmySoldiers($gameId, $result['armyId'], false, $db);
            if (empty($result['heroes']) && empty($result['soldiers'])) {
                $result['destroyed'] = true;
                self::destroyArmy($gameId, $result['armyId'], $result['playerId'], $db);
                return $result;
            } else {
                $result['movesLeft'] = self::calculateArmyMovesLeft($gameId, $result['armyId'], $db);
                return $result;
            }
        } catch (Exception $e) {
            echo($select->__toString());
        }
    }

    static public function destroyArmy($gameId, $armyId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $data = array(
            'destroyed' => 'true'
        );
        $where = array(
            $db->quoteInto('"armyId" = ?', $armyId),
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId)
        );
        try {
            return $db->update('army', $data, $where);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function ruinExists($gameId, $ruinId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $select = $db->select()
                ->from('ruin', 'ruinId')
                ->where('"ruinId" = ?', $ruinId)
                ->where('"gameId" = ?', $gameId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getPlayerColor($gameId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $select = $db->select()
                ->from('playersingame', 'color')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" = ?', $playerId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function addCastle($gameId, $castleId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $data = array(
            'castleId' => $castleId,
            'playerId' => $playerId,
            'gameId' => $gameId
        );
        try {
            return $db->insert('castle', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function getCastleDefenseModifier($gameId, $castleId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $select = $db->select()
                ->from('castle', 'defenseMod')
                ->where('"gameId" = ?', $gameId)
                ->where('"castleId" = ?', $castleId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($select->__toString());
        }
    }

    static public function getTurn($gameId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $select = $db->select()
                ->from(array('a' => 'game'), array('nr' => 'turnNumber'))
                ->join(array('b' => 'playersingame'), 'a."turnPlayerId" = b."playerId" AND a."gameId" = b."gameId"', array('color', 'lost'))
                ->where('a."gameId" = ?', $gameId);
        try {
            return $db->fetchRow($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function isEnemyCastle($gameId, $castleId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $select = $db->select()
                ->from('castle', 'castleId')
                ->where('razed = false')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" != ?', $playerId)
                ->where('"castleId" = ?', $castleId);
        try {
            $castleId = $db->fetchOne($select);
            if ($castleId !== null) {
                return true;
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getAllUnitsFromCastlePosition($gameId, $position, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $xs = array(
            $position['x'],
            $position['x'] + 1
        );
        $ys = array(
            $position['y'],
            $position['y'] + 1
        );
        $ids = '';
        $select = $db->select()
                ->from('army', 'armyId')
                ->where('"gameId" = ?', $gameId)
                ->where('destroyed = false')
                ->where('x IN (?)', $xs)
                ->where('y IN (?)', $ys);
        try {
            $result = $db->query($select)->fetchAll();
            foreach ($result as $id)
            {
                if ($ids) {
                    $ids .= ',';
                }
                $ids .= $id['armyId'];
            }
            if ($ids) {
                return array(
                    'heroes' => self::getArmyHeroes($gameId, $ids, true, $db),
                    'soldiers' => self::getArmySoldiers($gameId, $ids, true, $db)
                );
            } else {
                return array(
                    'heroes' => array(),
                    'soldiers' => array()
                );
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function updateAllArmiesFromCastlePosition($gameId, $position, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $xs = array(
            $position['x'],
            $position['x'] + 1
        );
        $ys = array(
            $position['y'],
            $position['y'] + 1
        );
        $select = $db->select()
                ->from('army')
                ->where('"gameId" = ?', $gameId)
                ->where('destroyed = false')
                ->where('x IN (?)', $xs)
                ->where('y IN (?)', $ys);
        try {
            $result = $db->query($select)->fetchAll();
            foreach ($result as $k => $army)
            {
                $heroes = self::getArmyHeroes($gameId, $army['armyId'], false, $db);
                $soldiers = self::getArmySoldiers($gameId, $army['armyId'], false, $db);
                if (empty($heroes) AND empty($soldiers)) {
                    self::destroyArmy($gameId, $army['armyId'], $army['playerId'], $db);
                    unset($result[$k]);
                } else {
                    $result[$k]['heroes'] = $heroes;
                    $result[$k]['soldiers'] = $soldiers;
                }
            }
            return $result;
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function changeOwner($gameId, $castleId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"castleId" = ?', $castleId)
        );
        $data = array(
            'defenseMod' => new Zend_Db_Expr('"defenseMod" - 1'),
            'playerId' => $playerId,
            'production' => null,
            'productionTurn' => 0,
        );
        try {
            return $db->update('castle', $data, $where);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function armyRemoveHero($gameId, $heroId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $data = array(
            'armyId' => null
        );
        $where = array(
            $db->quoteInto('"heroId" = ?', $heroId),
            $db->quoteInto('"gameId" = ?', $gameId),
        );
        try {
            return $db->update('heroesingame', $data, $where);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function razeCastle($gameId, $castleId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"castleId" = ?', $castleId),
            $db->quoteInto('"playerId" = ?', $playerId)
        );
        $data = array(
            'razed' => 'true',
            'production' => null,
            'productionTurn' => 0,
        );
        try {
            return $db->update('castle', $data, $where);
        } catch (Exception $e) {
            echo $e;
        }
    }

    static public function getPlayerInGameGold($gameId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $select = $db->select()
                ->from('playersingame', 'gold')
                ->where('"playerId" = ?', $playerId)
                ->where('"gameId" = ?', $gameId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function updatePlayerInGameGold($gameId, $playerId, $gold, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $data['gold'] = $gold;
        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId)
        );
        try {
            return $db->update('playersingame', $data, $where);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function getCastle($gameId, $castleId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $select = $db->select()
                ->from('castle')
                ->where('"gameId" = ?', $gameId)
                ->where('"castleId" = ?', $castleId);
        try {
            return $db->fetchRow($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function buildDefense($gameId, $castleId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId),
            $db->quoteInto('"castleId" = ?', $castleId)
        );
        $data = array(
            'defenseMod' => new Zend_Db_Expr('"defenseMod" + 1')
        );
        try {
            return $db->update('castle', $data, $where);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function getAllUnitsFromPosition($gameId, $position, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $ids = '';
        $select = $db->select()
                ->from('army')
                ->where('"gameId" = ?', $gameId)
                ->where('destroyed = false')
                ->where('x = (?)', $position['x'])
                ->where('y = (?)', $position['y']);
        try {
            $result = $db->query($select)->fetchAll();
            foreach ($result as $id)
            {
                if ($ids) {
                    $ids .= ',';
                }
                $ids .= $id['armyId'];
            }
            if ($ids) {
                $heroes = self::getArmyHeroes($gameId, $ids, true, $db);
                $soldiers = self::getArmySoldiers($gameId, $ids, true, $db);
                return array('heroes' => $heroes, 'soldiers' => $soldiers);
            } else {
                return array('heroes' => null, 'soldiers' => null);
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function updateAllArmiesFromPosition($gameId, $position, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $select = $db->select()
                ->from('army')
                ->where('"gameId" = ?', $gameId)
                ->where('destroyed = false')
                ->where('x = ?', $position['x'])
                ->where('y = ?', $position['y']);
        try {
            $result = $db->query($select)->fetchAll();
            foreach ($result as $k => $army)
            {
                $heroes = self::getArmyHeroes($gameId, $army['armyId'], false, $db);
                $soldiers = self::getArmySoldiers($gameId, $army['armyId'], false, $db);
                if (empty($heroes) && empty($soldiers)) {
                    self::destroyArmy($gameId, $army['armyId'], $army['playerId'], $db);
                    unset($result[$k]);
                } else {
                    $result[$k]['heroes'] = $heroes;
                    $result[$k]['soldiers'] = $soldiers;
                }
            }
            return $result;
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function destroySoldier($gameId, $soldierId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $where = array(
            $db->quoteInto('"soldierId" = ?', $soldierId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            $db->delete('soldier', $where);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function searchRuin($gameId, $ruinId, $heroId, $armyId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $turn = self::getTurn($gameId, $db);

        $random = rand(0, 100);
        if ($random < 10) {//10%
            //śmierć
            if ($turn['nr'] <= 7) {
                $find = array('null', 1);
                self::addRuin($gameId, $ruinId, $db);
            } else {
                $find = array('death', 1);
                self::armyRemoveHero($heroId);
            }
        } elseif ($random < 55) {//45%
            //kasa
            $gold = rand(50, 150);
            $find = array('gold', $gold);
            $inGameGold = self::getPlayerInGameGold($gameId, $playerId, $db);
            self::updatePlayerInGameGold($gameId, $playerId, $gold + $inGameGold, $db);
            self::zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db);
            self::addRuin($gameId, $ruinId, $db);
        } elseif ($random < 85) {//30%
            //jednostki
            if ($turn['nr'] <= 7) {
                $max1 = 11;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turn['nr'] <= 12) {
                $max1 = 13;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turn['nr'] <= 16) {
                $max1 = 14;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turn['nr'] <= 19) {
                $max1 = 15;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turn['nr'] <= 21) {
                $max1 = 15;
                $min2 = 1;
                $max2 = 2;
            } elseif ($turn['nr'] <= 23) {
                $max1 = 15;
                $min2 = 1;
                $max2 = 3;
            } elseif ($turn['nr'] <= 25) {
                $max1 = 15;
                $min2 = 2;
                $max2 = 3;
            } else {
                $max1 = 15;
                $min2 = 3;
                $max2 = 3;
            }
            $unitId = rand(11, $max1);
            $numerOfUnits = rand($min2, $max2);
            $find = array('alies', $numerOfUnits);
            for ($i = 0; $i < $numerOfUnits; $i++)
            {
                self::addSoldierToArmy($gameId, $armyId, $unitId, $db);
            }
            self::zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db);
            self::addRuin($gameId, $ruinId, $db);
        } elseif ($random < 95) {//10%
            //nic
            $find = array('null', 1);
            self::zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db);
            self::addRuin($gameId, $ruinId, $db);
        } else {//5%
            //artefakt
            $artefactId = rand(5, 34);

            if (Application_Model_Inventory::wsItemExists($gameId, $artefactId, $heroId, $db)) {
                Application_Model_Inventory::wsIncreaseItemQuantity($gameId, $artefactId, $heroId, $db);
            } else {
                Application_Model_Inventory::wsAddArtefact($gameId, $artefactId, $heroId, $db);
            }
            $find = array('artefact', $artefactId);
            self::zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db);
            self::addRuin($gameId, $ruinId, $db);
        }

        return $find;
    }

    static public function addRuin($gameId, $ruinId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $data = array(
            'ruinId' => $ruinId,
            'gameId' => $gameId
        );
        try {
            $db->insert('ruin', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        if ($heroId != self::getHeroIdByPlayerId($gameId, $playerId, $db)) {
            echo('HeroId jest inny');
            return;
        }
        $data = array(
            'movesLeft' => 0
        );
        $where = array(
            $db->quoteInto('"armyId" = ?', $armyId),
            $db->quoteInto('"heroId" = ?', $heroId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            return $db->update('heroesingame', $data, $where);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function getHeroIdByPlayerId($gameId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $select = $db->select()
                ->from(array('a' => 'hero'), 'heroId')
                ->join(array('b' => 'heroesingame'), 'a."heroId" = b."heroId"')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" = ?', $playerId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function addSoldierToArmy($gameId, $armyId, $unitId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $select = $db->select()
                ->from('unit', 'numberOfMoves')
                ->where('"unitId" = ?', $unitId);
        $data = array(
            'armyId' => $armyId,
            'gameId' => $gameId,
            'unitId' => $unitId,
            'movesLeft' => new Zend_Db_Expr('(' . $select->__toString() . ')')
        );
        try {
            return $db->insert('soldier', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function getHeroIdByArmyIdPlayerId($gameId, $armyId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $select = $db->select()
                ->from(array('a' => 'hero'), 'heroId')
                ->join(array('b' => 'heroesingame'), 'a."heroId" = b."heroId"')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" = ?', $playerId)
                ->where('"armyId" = ?', $armyId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getArmyPositionByArmyId($gameId, $armyId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $select = $db->select()
                ->from('army', array('x', 'y'))
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" = ?', $playerId)
                ->where('destroyed = false')
                ->where('"armyId" = ?', $armyId);
        try {
            return $db->fetchRow($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function splitArmy($gameId, $h, $s, $parentArmyId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $position = self::getArmyPositionByArmyId($gameId, $parentArmyId, $playerId, $db);
        $heroesIds = explode(',', $h);
        $soldiersIds = explode(',', $s);

        if ((isset($heroesIds[0]) && !empty($heroesIds[0])) || (isset($soldiersIds) && !empty($soldiersIds))) {
            $newArmyId = self::createArmy($gameId, array('x' => $position['x'], 'y' => $position['y']), $playerId, 0, $db);
            foreach ($heroesIds as $heroId)
            {
                if (!empty($heroId)) {
                    self::heroUpdateArmyId($gameId, $heroId, $newArmyId, $db);
                }
            }
            foreach ($soldiersIds as $soldierId)
            {
                if (!empty($soldierId)) {
                    self::soldierUpdateArmyId($gameId, $soldierId, $newArmyId, $db);
                }
            }
            return $newArmyId;
        }
    }

    static public function createArmy($gameId, $position, $playerId, $sleep = 0, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $armyId = self::getNewArmyId($gameId, $db);
        $data = array(
            'armyId' => $armyId,
            'playerId' => $playerId,
            'gameId' => $gameId,
            'x' => $position['x'],
            'y' => $position['y']
        );
        try {
            $db->insert('army', $data);
            return $armyId;
        } catch (Exception $e) {
            if ($sleep > 10) {
                echo($e);
                return;
            }
            sleep(rand(0, $sleep));
            $armyId = self::createArmy($gameId, $position, $playerId, $sleep + 1, $db);
        }
        return $armyId;
    }

    static private function getNewArmyId($gameId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $select = $db->select()
                ->from('army', 'max("armyId")')
                ->where('"gameId" = ?', $gameId);
        try {
            return $db->fetchOne($select) + 1;
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static private function heroUpdateArmyId($gameId, $heroId, $newArmyId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $data = array(
            'armyId' => $newArmyId
        );
        $where = array(
            $db->quoteInto('"heroId" = ?', $heroId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            return $db->update('heroesingame', $data, $where);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static private function soldierUpdateArmyId($gameId, $soldierId, $newArmyId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $data = array(
            'armyId' => $newArmyId
        );
        $where = array(
            $db->quoteInto('"soldierId" = ?', $soldierId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            return $db->update('soldier', $data, $where);
        } catch (Exception $e) {
            echo($e);
        }
    }

}

