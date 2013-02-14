<?php

class Cli_Database {

    static public function getDb() {
        return new Zend_Db_Adapter_Pdo_Pgsql(array(
                    'host' => Zend_Registry::get('config')->resources->db->params->host,
                    'username' => Zend_Registry::get('config')->resources->db->params->username,
                    'password' => Zend_Registry::get('config')->resources->db->params->password,
                    'dbname' => Zend_Registry::get('config')->resources->db->params->dbname
                ));
    }

    static public function update($name, $data, $where, $db, $quiet = false) {
        $updateResult = $db->update($name, $data, $where);
        switch ($updateResult)
        {
            case 1:
                return $updateResult;
                break;

            case 0:
                if ($quiet) {
                    return;
                }
                echo('
Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane
');
                self::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
                break;

            case null:
                echo('
Zapytanie zwróciło błąd
');
                self::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
                break;

            default:
                if ($quiet) {
                    return;
                }
                echo('
Został zaktualizowany więcej niż jeden rekord (' . $updateResult . ').
');
                self::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
                print_r($updateResult);
                break;
        }
    }

    static public function debug($debug) {
        if (true) {
            print_r($debug[1]);
        }
    }

    static public function isPlayerCastle($gameId, $castleId, $playerId, $db) {
        $select = $db->select()
                ->from('castle', 'castleId')
                ->where('razed = false')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" = ?', $playerId)
                ->where('"castleId" = ?', $castleId);
        try {
            $id = $db->fetchOne($select);

            if ($castleId == $id) {
                return true;
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function joinArmiesAtPosition($gameId, $position, $playerId, $db) {
        $select = $db->select()
                ->from('army', 'armyId')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" = ?', $playerId)
                ->where('destroyed = false')
                ->where('x = ?', $position['x'])
                ->where('y = ?', $position['y']);
        try {
            $result = $db->query($select)->fetchAll();
            if (!isset($result[0]['armyId'])) {
                echo 'Brak armii na pozycji';
                return array(
                    'armyId' => null,
                    'deletedIds' => null,
                );
            }
            $firstArmyId = $result[0]['armyId'];
            unset($result[0]);
            $count = count($result);
            for ($i = 1; $i <= $count; $i++)
            {
                if ($result[$i]['armyId'] == $firstArmyId) {
                    continue;
                }
                self::heroesUpdateArmyId($gameId, $result[$i]['armyId'], $firstArmyId, $db);
                self::soldiersUpdateArmyId($gameId, $result[$i]['armyId'], $firstArmyId, $db);
                self::destroyArmy($gameId, $result[$i]['armyId'], $playerId, $db);
            }
            return array(
                'armyId' => $firstArmyId,
                'deletedIds' => $result
            );
        } catch (Exception $e) {
            echo $e;
            echo($select->__toString());
        }
    }

    static private function heroesUpdateArmyId($gameId, $oldArmyId, $newArmyId, $db) {
        $data = array(
            'armyId' => $newArmyId
        );
        $where = array(
            $db->quoteInto('"armyId" = ?', $oldArmyId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            return self::update('heroesingame', $data, $where, $db, true);
        } catch (Exception $e) {
            echo $e;
        }
    }

    static private function soldiersUpdateArmyId($gameId, $oldArmyId, $newArmyId, $db) {
        $data = array(
            'armyId' => $newArmyId
        );
        $where = array(
            $db->quoteInto('"armyId" = ?', $oldArmyId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            return self::update('soldier', $data, $where, $db, true);
        } catch (Exception $e) {
            echo $e;
        }
    }

    static public function updateArmyPosition($gameId, $armyId, $playerId, $data, $db) {
        $data1 = array(
            'x' => $data['x'],
            'y' => $data['y'],
        );

        $select1 = $db->select()
                ->from('heroesingame', array('movesLeft', 'heroId'))
                ->where('"gameId" = ?', $gameId)
                ->where('"armyId" = ?', $armyId);
        try {
            $result1 = $db->query($select1)->fetchAll();
        } catch (Exception $e) {
            echo($e);
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
                self::update('heroesingame', $data2, $where1, $db);
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
            echo($e);
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
                self::update('soldier', $data2, $where1, $db);
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
            return self::update('army', $data1, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function getEnemyArmiesFieldsPositions($gameId, $playerId, $db) {
        $fields = Application_Model_Board::getBoardFields();

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

    static public function getArmyByArmyIdPlayerId($gameId, $armyId, $playerId, $db) {
        $select = $db->select()
                ->from('army', self::armyArray())
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" = ?', $playerId)
                ->where('destroyed = false')
                ->where('"armyId" = ?', $armyId);
        try {
            $result = $db->fetchRow($select);
            if (isset($result['armyId'])) {
                $result['heroes'] = self::getArmyHeroes($gameId, $armyId, false, $db);
                $result['soldiers'] = self::getArmySoldiers($gameId, $armyId, false, $db);
                $result['movesLeft'] = self::calculateArmyMovesLeft($gameId, $armyId, $db);
                return $result;
            }
        } catch (Exception $e) {
            echo($e);
            echo $select->__toString();
        }
    }

    static private function armyArray() {
        return array('armyId', 'destroyed', 'x', 'y', 'playerId');
    }

    static private function getArmyHeroes($gameId, $armyId, $in, $db) {
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

    static private function getArtefactsByHeroId($gameId, $heroId, $db) {
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

    static private function getArmySoldiers($gameId, $armyId, $in, $db) {
        $select = $db->select()
                ->from(array('a' => 'soldier'))
                ->join(array('b' => 'unit'), 'a."unitId" = b."unitId"', array('unitId', 'name', 'numberOfMoves', 'attackPoints', 'defensePoints', 'canFly', 'canSwim', 'cost'))
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

    static public function calculateArmyMovesLeft($gameId, $armyId, $db) {
        $heroMovesLeft = self::getMinHeroesMovesLeft($gameId, $armyId, $db);
        $soldierMovesLeft = self::getMinSoldiersMovesLeft($gameId, $armyId, $db);

        if ($soldierMovesLeft && $heroMovesLeft) {
            if ($heroMovesLeft > $soldierMovesLeft) {
                return $soldierMovesLeft;
            } else {
                return $heroMovesLeft;
            }
        } elseif ($soldierMovesLeft === null) {
            return (int) $heroMovesLeft;
        } elseif ($heroMovesLeft === null) {
            return (int) $soldierMovesLeft;
        }
        return 0;
    }

    static private function getMinHeroesMovesLeft($gameId, $armyId, $db) {
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

    static private function getMinSoldiersMovesLeft($gameId, $armyId, $db) {
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

    static public function getInGameWSSUIdsExceptMine($gameId, $playerId, $db) {
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

    static public function getInGameWSSUIds($gameId, $db) {
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

    static public function isPlayerTurn($gameId, $playerId, $db) {
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

    static public function getPlayerIdByColor($gameId, $color, $db) {
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

    static public function getPlayerArmies($gameId, $playerId, $db) {
        $select = $db->select()
                ->from('army', self::armyArray())
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

    static public function getArmyByArmyId($gameId, $armyId, $db) {
        $select = $db->select()
                ->from('army', self::armyArray())
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
                unset($result['playerId']);
                return $result;
            } else {
                unset($result['playerId']);
                $result['movesLeft'] = self::calculateArmyMovesLeft($gameId, $result['armyId'], $db);
                return $result;
            }
        } catch (Exception $e) {
            echo $e;
            echo($select->__toString());
        }
    }

    static public function destroyArmy($gameId, $armyId, $playerId, $db) {
        $data = array(
            'destroyed' => 'true'
        );
        $where = array(
            $db->quoteInto('"armyId" = ?', $armyId),
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId)
        );
        try {
            return self::update('army', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function ruinExists($gameId, $ruinId, $db) {
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

    static public function getColorByPlayerId($gameId, $playerId, $db) {
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

    static public function addCastle($gameId, $castleId, $playerId, $db) {
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

    static public function getCastleDefenseModifier($gameId, $castleId, $db) {
        $select = $db->select()
                ->from('castle', 'defenseMod')
                ->where('"gameId" = ?', $gameId)
                ->where('"castleId" = ?', $castleId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getTurn($gameId, $db) {
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

    static public function isEnemyCastle($gameId, $castleId, $playerId, $db) {

        $select = $db->select()
                ->from('castle', 'castleId')
                ->where('razed = false')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" != ?', $playerId)
                ->where('"castleId" = ?', $castleId);
        try {
            $id = $db->fetchOne($select);
            if ($castleId == $id) {
                return true;
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getAllUnitsFromCastlePosition($gameId, $position, $db) {

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
                    'soldiers' => self::getArmySoldiers($gameId, $ids, true, $db),
                    'ids' => explode(',', $ids)
                );
            } else {
                return array(
                    'heroes' => array(),
                    'soldiers' => array(),
                    'ids' => array()
                );
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function updateAllArmiesFromCastlePosition($gameId, $position, $db) {

        $xs = array(
            $position['x'],
            $position['x'] + 1
        );
        $ys = array(
            $position['y'],
            $position['y'] + 1
        );
        $select = $db->select()
                ->from('army', self::armyArray())
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
                    unset($result[$k]['playerId']);
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

    static public function changeOwner($gameId, $castleId, $playerId, $db) {
        $defenseMod = self::getCastleDefenseModifier($gameId, $castleId, $db);
        var_dump($defenseMod);
        if ($defenseMod > 1) {
            $defenseMod--;
        }
        var_dump($defenseMod);

        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"castleId" = ?', $castleId)
        );
        $data = array(
            'defenseMod' => $defenseMod,
            'playerId' => $playerId,
            'production' => null,
            'productionTurn' => 0,
        );
        try {
            return self::update('castle', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function armyRemoveHero($gameId, $heroId, $db) {

        $data = array(
            'armyId' => null
        );
        $where = array(
            $db->quoteInto('"heroId" = ?', $heroId),
            $db->quoteInto('"gameId" = ?', $gameId),
        );
        try {
            return self::update('heroesingame', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function razeCastle($gameId, $castleId, $playerId, $db) {

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
            return self::update('castle', $data, $where, $db);
        } catch (Exception $e) {
            echo $e;
        }
    }

    static public function getPlayerInGameGold($gameId, $playerId, $db) {

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

    static public function updatePlayerInGameGold($gameId, $playerId, $gold, $db) {

        $data['gold'] = $gold;
        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId)
        );
        try {
            return self::update('playersingame', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function getCastle($gameId, $castleId, $db) {

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

    static public function buildDefense($gameId, $castleId, $playerId, $db) {

        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId),
            $db->quoteInto('"castleId" = ?', $castleId)
        );
        $data = array(
            'defenseMod' => new Zend_Db_Expr('"defenseMod" + 1')
        );
        try {
            return self::update('castle', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function getAllEnemyUnitsFromPosition($gameId, $position, $playerId, $db) {

        $ids = '';
        $select = $db->select()
                ->from('army', 'armyId')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" != ?', $playerId)
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
                return array(
                    'heroes' => $heroes,
                    'soldiers' => $soldiers,
                    'ids' => explode(',', $ids)
                );
            } else {
                return array(
                    'heroes' => null,
                    'soldiers' => null,
                    'armyId' => null,
                    'ids' => null
                );
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function areMySwimmingUnitsAtPosition($gameId, $position, $playerId, $db) {

        $ids = '';
        $select = $db->select()
                ->from('army', 'armyId')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" = ?', $playerId)
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
            if (!$ids) {
                return;
            }
            return self::getSwimmingSoldiersFromArmiesIds($gameId, $ids, $db);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getSwimmingSoldiersFromArmiesIds($gameId, $ids, $db) {
        $select = $db->select()
                ->from(array('a' => 'soldier'), null)
                ->join(array('b' => 'unit'), 'a."unitId" = b."unitId"', 'canSwim')
                ->where('"canSwim" = true')
                ->where('"gameId" = ?', $gameId)
                ->where('"armyId" IN (?)', new Zend_Db_Expr($ids));
        try {
            return $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function updateAllArmiesFromPosition($gameId, $position, $db) {

        $select = $db->select()
                ->from('army', self::armyArray())
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
                    unset($result[$k]['playerId']);
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

    static public function destroySoldier($gameId, $soldierId, $db) {

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

    static public function searchRuin($gameId, $ruinId, $heroId, $armyId, $playerId, $db) {

        $turn = self::getTurn($gameId, $db);

        $random = rand(0, 100);
        if ($random < 10) {//10%
//śmierć
            if ($turn['nr'] <= 7) {
                $find = array('null', 1);
                self::addRuin($gameId, $ruinId, $db);
                self::zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db);
            } else {
                $find = array('death', 1);
                self::armyRemoveHero($gameId, $heroId, $db);
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

            if (Cli_Inventory::itemExists($gameId, $artefactId, $heroId, $db)) {
                Cli_Inventory::increaseItemQuantity($gameId, $artefactId, $heroId, $db);
            } else {
                Cli_Inventory::addArtefact($gameId, $artefactId, $heroId, $db);
            }
            $find = array('artefact', $artefactId);
            self::zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db);
            self::addRuin($gameId, $ruinId, $db);
        }

        return $find;
    }

    static public function addRuin($gameId, $ruinId, $db) {

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

    static public function zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db) {

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
            return self::update('heroesingame', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function getHeroIdByPlayerId($gameId, $playerId, $db) {

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

    static public function addSoldierToArmy($gameId, $armyId, $unitId, $db) {

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

    static public function getHeroIdByArmyIdPlayerId($gameId, $armyId, $playerId, $db) {

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

    static public function getArmyPositionByArmyId($gameId, $armyId, $playerId, $db) {

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

    static public function splitArmy($gameId, $h, $s, $parentArmyId, $playerId, $db) {

        $position = self::getArmyPositionByArmyId($gameId, $parentArmyId, $playerId, $db);
        $heroesIds = explode(',', $h);
        $soldiersIds = explode(',', $s);

        if ((isset($heroesIds[0]) && !empty($heroesIds[0])) || (isset($soldiersIds) && !empty($soldiersIds))) {
            $newArmyId = self::createArmy($gameId, $db, array('x' => $position['x'], 'y' => $position['y']), $playerId);
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

    static public function createArmy($gameId, $db, $position, $playerId, $sleep = 0) {

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
            $armyId = self::createArmy($gameId, $db, $position, $playerId, $sleep + 1);
        }
        return $armyId;
    }

    static private function getNewArmyId($gameId, $db) {

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

    static private function heroUpdateArmyId($gameId, $heroId, $newArmyId, $db) {

        $data = array(
            'armyId' => $newArmyId
        );
        $where = array(
            $db->quoteInto('"heroId" = ?', $heroId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            return self::update('heroesingame', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static private function soldierUpdateArmyId($gameId, $soldierId, $newArmyId, $db) {

        $data = array(
            'armyId' => $newArmyId
        );
        $where = array(
            $db->quoteInto('"soldierId" = ?', $soldierId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            return self::update('soldier', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function isHeroInGame($gameId, $playerId, $db) {

        $select = $db->select()
                ->from(array('a' => 'hero'), 'heroId')
                ->join(array('b' => 'heroesingame'), 'a."heroId" = b."heroId"')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" = ?', $playerId);
        try {
            $heroId = $db->fetchOne($select);
            if ($heroId !== null) {
                return true;
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function connectHero($gameId, $playerId, $db) {

        $select = $db->select()
                ->from('hero', 'heroId')
                ->where('"playerId" = ?', $playerId);
        try {
            $heroId = $db->fetchOne($select);
            $data = array(
                'armyId' => null,
                'gameId' => $gameId,
                'heroId' => $heroId
            );
            return $db->insert('heroesingame', $data);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getDeadHeroId($gameId, $playerId, $db) {

        $select = $db->select()
                ->from(array('a' => 'hero'), 'heroId')
                ->join(array('b' => 'heroesingame'), 'a."heroId" = b."heroId"', 'armyId')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" = ?', $playerId);
        try {
            $result = $db->fetchRow($select);
            if (!isset($result['armyId'])) {
                return $result['heroId'];
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function heroResurection($gameId, $heroId, $position, $playerId, $db) {

        $armyId = self::getArmyIdFromPosition($gameId, $position, $db);
        if (!$armyId) {
            $armyId = self::createArmy($gameId, $db, $position, $playerId);
        }
        self::addHeroToArmy($gameId, $armyId, $heroId, 0, $db);
        return $armyId;
    }

    static public function getArmyIdFromPosition($gameId, $position, $db) {

        $select = $db->select()
                ->from('army', 'armyId')
                ->where('"gameId" = ?', $gameId)
                ->where('destroyed = false')
                ->where('x = ?', $position['x'])
                ->where('y = ?', $position['y']);
        try {
            $result = $db->fetchRow($select);
            if (isset($result['armyId'])) {
                return $result['armyId'];
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function addHeroToArmy($gameId, $armyId, $heroId, $movesLeft, $db) {

        $data = array(
            'armyId' => $armyId,
            'movesLeft' => $movesLeft
        );
        $where = array(
            $db->quoteInto('"heroId" = ?', $heroId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            return self::update('heroesingame', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function areUnitsAtCastlePosition($gameId, $position, $db) {
        $xs = array(
            $position['x'],
            $position['x'] + 1
        );
        $ys = array(
            $position['y'],
            $position['y'] + 1
        );
        $select = $db->select()
                ->from('army', 'armyId')
                ->where('"gameId" = ?', $gameId)
                ->where('destroyed = false')
                ->where('x IN (?)', $xs)
                ->where('y IN (?)', $ys);
        try {
            return $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function isGameMaster($gameId, $playerId, $db) {

        $select = $db->select()
                ->from('game', array('gameMasterId'))
                ->where('"gameId" = ?', $gameId)
                ->where('"gameMasterId" = ?', $playerId);
        try {
            $gameMasterId = $db->fetchOne($select);
            if ($playerId == $gameMasterId) {
                return true;
            }
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function getAllPlayerArmiesExeptOne($gameId, $armyId, $playerId, $db) {

        $select = $db->select()
                ->from('army', array('x', 'y'))
                ->where('"gameId" = ?', $gameId)
                ->where('"armyId" != ?', $armyId)
                ->where('"playerId" = ?', $playerId)
                ->where('destroyed = false');
        try {
            return $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getTurnPlayerId($gameId, $db) {

        $select = $db->select()
                ->from('game', 'turnPlayerId')
                ->where('"gameId" = ?', $gameId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function isComputer($playerId, $db) {
        $select = $db->select()
                ->from('player', 'computer')
                ->where('"playerId" = ?', $playerId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function playerTurnActive($gameId, $playerId, $db) {

        $select = $db->select()
                ->from('playersingame', 'turnActive')
                ->where('"playerId" = ?', $playerId)
                ->where('"turnActive" = ?', true)
                ->where('"gameId" = ?', $gameId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function turnActivate($gameId, $playerId, $db) {

        $data = array(
            'turnActive' => 'true'
        );
        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId)
        );
        try {
            self::update('playersingame', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
        $data['turnActive'] = 'false';
        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"turnActive" = ?', 'true'),
            $db->quoteInto('"playerId" != ?', $playerId)
        );
        try {
            self::update('playersingame', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function resetHeroesMovesLeft($gameId, $playerId, $db) {

        $heroId = self::getHeroIdByPlayerId($gameId, $playerId, $db);
        $select = $db->select()
                ->from('hero', 'numberOfMoves')
                ->where('"playerId" = ?', $playerId);
        $data = array(
            'movesLeft' => new Zend_Db_Expr('(' . $select->__toString() . ')')
        );
        $where = array(
            $db->quoteInto('"heroId" = ?', $heroId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            self::update('heroesingame', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function resetSoldiersMovesLeft($gameId, $playerId, $db) {
        $select1 = $db->select()
                ->from('unit', 'numberOfMoves')
                ->where('soldier."unitId" = unit."unitId"');
        $data = array(
            'movesLeft' => new Zend_Db_Expr('(' . $select1->__toString() . ')')
        );
        $select2 = $db->select()
                ->from('army', 'armyId')
                ->where('"playerId" = ?', $playerId)
                ->where('destroyed = false')
                ->where('"gameId" = ?', $gameId);
        $where = array(
            $db->quoteInto('"armyId" IN (?)', $select2),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            return self::update('soldier', $data, $where, $db, true);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function getComputerArmyToMove($gameId, $playerId, $db) {

        $armies = self::getPlayerArmies($gameId, $playerId, $db);
        foreach ($armies as $army)
        {
            $army['movesLeft'] = self::calculateArmyMovesLeft($gameId, $army['armyId'], $db);
            if ($army['movesLeft'] > 0) {
                return $army;
            }
        }
    }

    static public function getTurnNumber($gameId, $db) {

        $select = $db->select()
                ->from('game', 'turnNumber')
                ->where('"gameId" = ?', $gameId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getPlayerCastles($gameId, $playerId, $db) {

        $playersCastles = array();
        $select = $db->select()
                ->from('castle')
                ->where('"playerId" = ?', $playerId)
                ->where('"gameId" = ?', $gameId)
                ->where('razed = false');
        try {
            $result = $db->query($select)->fetchAll();
            foreach ($result as $val)
            {
                $playersCastles[$val['castleId']] = $val;
            }
            return $playersCastles;
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getAllCastles($gameId, $db) {

        $castles = array();
        $select = $db->select()
                ->from('castle')
                ->where('"gameId" = ?', $gameId);
        try {
            foreach ($db->query($select)->fetchAll() as $val)
            {
                $castles[$val['castleId']] = $val;
            }
            return $castles;
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getCastleProduction($gameId, $castleId, $playerId, $db) {

        $select = $db->select()
                ->from('castle', array('production', 'productionTurn'))
                ->where('"gameId" = ?', $gameId)
                ->where('"castleId" = ?', $castleId)
                ->where('"playerId" = ?', $playerId);
        try {
            return $db->fetchRow($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function setCastleProduction($gameId, $castleId, $unitId, $playerId, $db) {

        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"castleId" = ?', $castleId),
            $db->quoteInto('"playerId" = ?', $playerId)
        );
        $data = array(
            'production' => $unitId,
            'productionTurn' => 0
        );
        try {
            return self::update('castle', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function resetProductionTurn($gameId, $castleId, $playerId, $db) {

        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId),
            $db->quoteInto('"castleId" = ?', $castleId)
        );
        $data = array(
            'productionTurn' => 0
        );
        try {
            return self::update('castle', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function enemiesCastlesExist($gameId, $playerId, $db) {

        $select = $db->select()
                ->from('castle', 'castleId')
                ->where('"playerId" != ?', $playerId)
                ->where('"gameId" = ?', $gameId)
                ->where('razed = false');
        try {
            $result = $db->query($select)->fetchAll();
            if (count($result)) {
                return true;
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function zeroArmyMovesLeft($gameId, $armyId, $db) {
        $data = array(
            'movesLeft' => 0
        );
        $where = array(
            $db->quoteInto('"armyId" = ?', $armyId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            self::update('soldier', $data, $where, $db, true);
            self::update('heroesingame', $data, $where, $db, true);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function getFull($gameId, $db) {

        $select = $db->select()
                ->from('ruin', 'ruinId')
                ->where('"gameId" = ?', $gameId);
        try {
            $result = $db->query($select)->fetchAll();
            $ruins = Application_Model_Board::getRuins();
            foreach ($result as $row)
            {
                if (isset($ruins[$row['ruinId']])) {
                    unset($ruins[$row['ruinId']]);
                }
            }
            return $ruins;
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getRazedCastles($gameId, $db) {

        $castles = array();
        $select = $db->select()
                ->from('castle')
                ->where('"gameId" = ?', $gameId)
                ->where('razed = true');
        try {
            $result = $db->query($select)->fetchAll();
            foreach ($result as $val)
            {
                $castles[$val['castleId']] = $val;
            }
            return $castles;
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getAllEnemiesArmies($gameId, $playerId, $db) {

        $select = $db->select()
                ->from('army', self::armyArray())
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" != ?', $playerId)
                ->where('destroyed = false');
        try {
            $result = $db->query($select)->fetchAll();
            $armies = array();
            foreach ($result as $k => $army)
            {
                $armies['army' . $army['armyId']] = $army;
                $armies['army' . $army['armyId']]['heroes'] = self::getArmyHeroes($gameId, $army['armyId'], false, $db);
                $armies['army' . $army['armyId']]['soldiers'] = self::getArmySoldiers($gameId, $army['armyId'], false, $db);
                if (empty($armies['army' . $army['armyId']]['heroes']) AND empty($armies['army' . $army['armyId']]['soldiers'])) {
                    self::destroyArmy($gameId, $armies['army' . $army['armyId']]['armyId'], $playerId, $db);
                    unset($armies['army' . $army['armyId']]);
                } else {
                    $armies['army' . $army['armyId']]['numberOfMoves'] = self::calculateMaxArmyMoves($gameId, $army['armyId'], $db);
                }
            }
            return $armies;
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function calculateMaxArmyMoves($gameId, $armyId, $db) {
        $heroMoves = self::getMaxHeroesMoves($gameId, $armyId, $db);
        $soldierMoves = self::getMaxSoldiersMoves($gameId, $armyId, $db);
        if ($heroMoves > $soldierMoves) {
            return $heroMoves;
        } else {
            return $soldierMoves;
        }
    }

    static private function getMaxHeroesMoves($gameId, $armyId, $db) {

        $select = $db->select()
                ->from(array('a' => 'hero'), 'max("numberOfMoves")')
                ->join(array('b' => 'heroesingame'), 'a."heroId" = b."heroId"', '')
                ->where('"gameId" = ?', $gameId)
                ->where('"armyId" = ?', $armyId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static private function getMaxSoldiersMoves($gameId, $armyId, $db) {

        $select = $db->select()
                ->from(array('a' => 'unit'), 'max("numberOfMoves")')
                ->join(array('b' => 'soldier'), 'a."unitId" = b."unitId"', '')
                ->where('"gameId" = ?', $gameId)
                ->where('"armyId" = ?', $armyId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static $playerColors = array('white', 'yellow', 'green', 'red', 'orange');

    static public function getExpectedNextTurnPlayer($gameId, $playerColor, $db) {

        $find = false;

        /* szukam następnego koloru w dostępnych kolorach */
        foreach (self::$playerColors as $color)
        {
            /* znajduję kolor gracza, który ma aktualnie turę i przewijam na następny */
            if ($playerColor == $color) {
                $find = true;
                continue;
            }

            /* to jest przewinięty kolor gracza */
            if ($find) {
                $nextPlayerColor = $color;
                break;
            }
        }

        if (!isset($nextPlayerColor)) {
            echo('Błąd! Nie znalazłem koloru gracza');
            return;
        }

        $playersInGame = self::getPlayersInGameReady($gameId, $db);

        /* przypisuję playerId do koloru */
        foreach ($playersInGame as $k => $player)
        {
            if ($player['color'] == $nextPlayerColor) {
                $nextPlayerId = $player['playerId'];
                break;
            }
        }

        /* jeśli nie znalazłem następnego gracza to następnym graczem jest gracz pierwszy */
        if (!isset($nextPlayerId)) {
            foreach ($playersInGame as $k => $player)
            {
                if ($player['color'] == self::$playerColors[0]) {
                    if ($player['lost']) {
                        $nextPlayerId = $playersInGame[$k + 1]['playerId'];
                        $nextPlayerColor = $playersInGame[$k + 1]['color'];
                    } else {
                        $nextPlayerId = $player['playerId'];
                        $nextPlayerColor = $player['color'];
                    }
                    break;
                }
            }
        }

        if (!isset($nextPlayerId)) {
            echo('Błąd! Nie znalazłem gracza');
            return;
        }

        return array(
            'playerId' => $nextPlayerId,
            'color' => $nextPlayerColor
        );
    }

    static public function getPlayersInGameReady($gameId, $db) {

        $select = $db->select()
                ->from(array('a' => 'playersingame'))
                ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', array('computer'))
                ->where('color is not null')
                ->where('a."gameId" = ?', $gameId);
        try {
            return $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function playerCastlesExists($gameId, $playerId, $db) {

        $select = $db->select()
                ->from('castle', 'castleId')
                ->where('"playerId" = ?', $playerId)
                ->where('"gameId" = ?', $gameId)
                ->where('razed = false');
        try {
            $result = $db->query($select)->fetchAll();
            if (count($result)) {
                return true;
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function playerArmiesExists($gameId, $playerId, $db) {

        $select = $db->select()
                ->from('army', 'armyId')
                ->where('"gameId" = ?', $gameId)
                ->where('destroyed = false')
                ->where('"playerId" = ?', $playerId);
        try {
            $result = $db->query($select)->fetchAll();
            if (count($result)) {
                return true;
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function endGame($gameId, $db) {
        $data['isActive'] = 'false';

        self::updateGame($gameId, $data, $db);
    }

    static public function updateGame($gameId, $data, $db) {

        $where = $db->quoteInto('"gameId" = ?', $gameId);
        try {
            return self::update('game', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function updateTurnNumber($gameId, $playerId, $db) {

        if (self::isGameMaster($gameId, $playerId, $db)) {
            $select = $db->select()
                    ->from('game', array('turnNumber' => '("turnNumber" + 1)'))
                    ->where('"gameId" = ?', $gameId);
            try {
                $result = $db->fetchRow($select);
                $data = array(
                    'turnNumber' => $result['turnNumber'],
                    'end' => new Zend_Db_Expr('now()')
                );
            } catch (Exception $e) {
                echo($e);
                echo($select->__toString());
            }
        }
        $data['turnPlayerId'] = $playerId;

        if (self::updateGame($gameId, $data, $db) == 1) {
            if (isset($data['turnNumber']) && Zend_Validate::is($data['turnNumber'], 'Digits')) {
                return $data['turnNumber'];
            }
        } else {
            echo('Błąd zapytania!');
        }
    }

    static public function raiseAllCastlesProductionTurn($gameId, $playerId, $db) {
        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId)
        );
        $data = array(
            'productionTurn' => new Zend_Db_Expr('"productionTurn" + 1')
        );
        try {
            return self::update('castle', $data, $where, $db, true);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function setPlayerLostGame($gameId, $playerId, $db) {

        $data['lost'] = 'true';
        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId)
        );
        try {
            self::update('playersingame', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function getColorByArmyId($gameId, $armyId, $db) {

        $select = $db->select()
                ->from('army', 'playerId')
                ->where('"gameId" = ?', $gameId)
                ->where('"armyId" = ?', $armyId);
        try {
            $playerId = $db->fetchOne($select);
            if ($playerId) {
                return self::getColorByPlayerId($gameId, $playerId, $db);
            } else {
                print_r(debug_backtrace(0, 2));
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getColorByCastleId($gameId, $castleId, $db) {

        $select = $db->select()
                ->from('castle', 'playerId')
                ->where('"gameId" = ?', $gameId)
                ->where('"castleId" = ?', $castleId);
        try {
            $playerId = $db->fetchOne($select);
            if ($playerId) {
                return self::getColorByPlayerId($gameId, $playerId, $db);
            } else {
                print_r(debug_backtrace(0, 2));
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getUnitIdByName($name, $db) {

        $select = $db->select()
                ->from('unit', 'unitId')
                ->where('name = ?', $name);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function playerLost($gameId, $playerId, $db) {

        $select = $db->select()
                ->from('playersingame', 'lost')
                ->where('"playerId" = ?', $playerId)
                ->where('lost = ?', true)
                ->where('"gameId" = ?', $gameId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function checkAccessKey($gameId, $playerId, $accessKey, $db) {

        $select = $db->select()
                ->from('playersingame', 'playerId')
                ->where('"playerId" = ?', $playerId)
                ->where('"gameId" = ?', $gameId)
                ->where('"accessKey" = ?', $accessKey);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function updatePlayerInGameWSSUId($gameId, $playerId, $wssuid, $db) {

        $data = array(
            'webSocketServerUserId' => $wssuid
        );
        $where = array(
            $db->quoteInto('"playerId" = ?', $playerId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            return self::update('playersingame', $data, $where, $db);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function getPlayersWaitingForGame($gameId, $db) {

        $select = $db->select()
                ->from(array('a' => 'playersingame'), array('color', 'playerId'))
                ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', array('firstName', 'lastName', 'computer'))
                ->where('a."gameId" = ?', $gameId)
                ->where('"webSocketServerUserId" IS NOT NULL OR computer = true');
        try {
            return $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getGameMasterId($gameId, $db) {
        $select = $db->select()
                ->from('game', 'gameMasterId')
                ->where('"gameId" = ?', $gameId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function isColorInGame($gameId, $color, $db) {
        $select = $db->select()
                ->from(array('a' => 'playersingame'), 'min(b."playerId")')
                ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
                ->where('"gameId" = ?', $gameId)
                ->where('color = ?', $color)
                ->where('"webSocketServerUserId" IS NOT NULL OR computer = true');
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getComputerPlayerId($gameId, $db) {
        $select = $db->select()
                ->from(array('a' => 'playersingame'), 'min(b."playerId")')
                ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
                ->where('"gameId" != ?', $gameId)
                ->where('color IS NOT NULL')
                ->where('computer = true');
        $ids = self::getComputerPlayersIds($gameId, $db);
        if ($ids) {
            $select->where('a."playerId" NOT IN (?)', new Zend_Db_Expr($ids));
        }
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getComputerPlayersIds($gameId, $db) {
        $ids = '';
        $select = $db->select()
                ->from(array('a' => 'playersingame'), 'playerId')
                ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
                ->where('a."gameId" = ?', $gameId)
                ->where('color IS NOT NULL')
                ->where('computer = true');
        try {
            foreach ($db->query($select)->fetchAll() as $row)
            {
                if ($ids) {
                    $ids .= ',';
                }
                $ids .= $row['playerId'];
            }
            return $ids;
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function createPlayer($data, $db) {
        try {
            $db->insert('player', $data);
            $seq = $db->quoteIdentifier('player_playerId_seq');
            return $db->lastSequenceId($seq);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function createComputerPlayer($db) {
        $data = array(
            'firstName' => 'Computer',
            'lastName' => 'Player',
            'computer' => 'true'
        );
        return self::createPlayer($data, $db);
    }

    static public function createHero($playerId, $db) {
        $data = array(
            'playerId' => $playerId
        );
        try {
            $db->insert('hero', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function joinGame($gameId, $playerId, $db) {
        $data = array(
            'gameId' => $gameId,
            'playerId' => $playerId,
            'accessKey' => self::generateKey()
        );
        try {
            $db->insert('playersingame', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function updatePlayerReady($gameId, $playerId, $color, $db) {
        if ($color && self::getColorByPlayerId($gameId, $playerId, $db) == $color) {
            $data['color'] = null;
        } else {
            $data['color'] = $color;
        }

        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId)
        );

        self::update('playersingame', $data, $where, $db);
    }

    static private function generateKey() {
        return md5(rand(0, time()));
    }

    static public function disconnectNotActive($gameId, $db) {
        $select = $db->select()
                ->from(array('a' => 'playersingame'), 'playerId')
                ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', '')
                ->where('"gameId" = ?', $gameId)
                ->where('"webSocketServerUserId" IS NULL')
                ->where('computer = false');
        $where = array(
            $db->quoteInto('"playerId" IN (?)', new Zend_Db_Expr($select->__toString())),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            $db->delete('playersingame', $where);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function disconnectFromGame($gameId, $playerId, $db) {
        $where = array(
            $db->quoteInto('"playerId" = ?', $playerId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );
        try {
            $db->delete('playersingame', $where);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function findNewGameMaster($gameId, $db) {
        $select = $db->select()
                ->from(array('a' => 'playersingame'), 'playerId')
                ->where('"gameId" = ?', $gameId)
                ->where('"webSocketServerUserId" IS NOT NULL');
        try {
            $gameMasterId = $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
            return;
        }
        if ($gameMasterId) {
            $data = array(
                'gameMasterId' => $gameMasterId
            );
            self::updateGame($gameId, $data, $db);
        }
    }

    static public function startGame($gameId, $db) {
        $data = array(
            'turnPlayerId' => self::getPlayerIdByColor($gameId, 'white', $db),
            'isOpen' => 'false'
        );
        self::updateGame($gameId, $data, $db);
    }

    static public function getComputerPlayers($gameId, $db) {
        $select = $db->select()
                ->from(array('a' => 'playersingame'), array('color', 'playerId'))
                ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', array('computer'))
                ->where('a."gameId" = ?', $gameId)
                ->where('color IS NOT NULL')
                ->where('computer = true');
        try {
            return $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getHeroes($playerId, $db) {
        $select = $db->select()
                ->from('hero')
                ->where('"playerId" = ?', $playerId);
        try {
            return $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function addHeroToGame($gameId, $armyId, $heroId, $db) {
        $data = array(
            'heroId' => $heroId,
            'armyId' => $armyId,
            'gameId' => $gameId,
            'movesLeft' => 16
        );
        try {
            return $db->insert('heroesingame', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function isGameStarted($gameId, $db) {
        $select = $db->select()
                ->from('game', 'gameId')
                ->where('"isOpen" = false')
                ->where('"gameId" = ?', $gameId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function isPlayerInGame($gameId, $playerId, $db) {
        $select = $db->select()
                ->from('playersingame', 'gameId')
                ->where('"gameId" = ?', $gameId)
                ->where('"playerId" = ?', $playerId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

}

