<?php

class Cli_Model_Database
{

    static public function getDb()
    {
        return new Zend_Db_Adapter_Pdo_Pgsql(array(
            'host' => Zend_Registry::get('config')->resources->db->params->host,
            'username' => Zend_Registry::get('config')->resources->db->params->username,
            'password' => Zend_Registry::get('config')->resources->db->params->password,
            'dbname' => Zend_Registry::get('config')->resources->db->params->dbname
        ));
    }

    static public function update($name, $data, $where, $db, $quiet = false)
    {
        try {
            $updateResult = $db->update($name, $data, $where);
        } catch (Exception $e) {
            echo($e);

            return;
        }
        switch ($updateResult) {
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
                Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
                break;

            case null:
                echo('
Zapytanie zwróciło błąd
');
                Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
                break;

            default:
                if ($quiet) {
                    return;
                }
                echo('
Został zaktualizowany więcej niż jeden rekord (' . $updateResult . ').
');
                Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
                print_r($updateResult);
                break;
        }
    }

    static public function isPlayerCastle($gameId, $castleId, $playerId, $db)
    {
        $select = $db->select()
            ->from('castlesingame', 'castleId')
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

    static public function joinArmiesAtPosition($gameId, $position, $playerId, $db)
    {
        if (!isset($position['x'])) {
            echo('
Brak x
');
            return;
        }
        if (!isset($position['y'])) {
            echo('
Brak y
');
            return;
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
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
        if (!isset($result[0]['armyId'])) {
            echo '
(joinArmiesAtPosition) Brak armii na pozycji: ';
            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
            print_r($position);

            return array(
                'armyId' => null,
                'deletedIds' => null,
            );
        }
        $firstArmyId = $result[0]['armyId'];
        unset($result[0]);
        $count = count($result);
        for ($i = 1; $i <= $count; $i++) {
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
    }

    static private function heroesUpdateArmyId($gameId, $oldArmyId, $newArmyId, $db)
    {
        $data = array(
            'armyId' => $newArmyId
        );
        $where = array(
            $db->quoteInto('"armyId" = ?', $oldArmyId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );

        return self::update('heroesingame', $data, $where, $db, true);
    }

    static private function soldiersUpdateArmyId($gameId, $oldArmyId, $newArmyId, $db)
    {
        $data = array(
            'armyId' => $newArmyId
        );
        $where = array(
            $db->quoteInto('"armyId" = ?', $oldArmyId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );

        return self::update('soldier', $data, $where, $db, true);
    }

    static public function updateArmyPosition($gameId, $playerId, $path, $fields, $army, $db)
    {
        if (empty($path)) {
            return;
        }

        $units = Zend_Registry::get('units');
        $terrainCosts = Cli_Model_Army::getTerrainCosts();

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

//        $selectHeroes = $db->select()
//            ->from('heroesingame', array('movesLeft', 'heroId'))
//            ->where('"gameId" = ?', $gameId)
//            ->where('"armyId" = ?', $army['armyId']);
//        try {
//            $heroes = $db->query($selectHeroes)->fetchAll();
//        } catch (Exception $e) {
//            echo($e);
//            echo($selectHeroes->__toString());
//
//            return;
//        }

        $heroes = $army['heroes'];

        foreach ($heroes as $hero) {
            $movesSpend = 0;
            if ($army['canFly'] > 0) {
                $type = 'flying';
            } elseif ($army['canSwim']) {
                $type = 'swimming';
            } else {
                $type = 'walking';
            }

            foreach ($path as $step) {
                $movesSpend += $terrainCosts[$type][$fields[$step['y']][$step['x']]];
            }

            $movesLeft = $hero['movesLeft'] - $movesSpend;
            if ($movesLeft < 0) {
                $movesLeft = 0;
            }

            $mHeroesInGame->updateMovesLeft($movesLeft, $hero['heroId']);
        }

//        $mSoldier = new Application_Model_Soldier($gameId, $db);
//        $soldiers = $mSoldier->getForArmyPosition($army['armyId']);

        $soldiers = $army['soldiers'];
        $mSoldier = new Application_Model_Soldier($gameId, $db);

        foreach ($soldiers as $soldier) {
            $movesSpend = 0;
            if ($army['canFly'] > 0) {
                $type = 'flying';
            } elseif ($army['canSwim']) {
                $type = 'swimming';
            } else {
                $type = 'walking';
                $terrainCosts[$type]['f'] = $units[$soldier['unitId']]['modMovesForest'];
                $terrainCosts[$type]['m'] = $units[$soldier['unitId']]['modMovesHills'];
                $terrainCosts[$type]['s'] = $units[$soldier['unitId']]['modMovesSwamp'];
            }

            foreach ($path as $step) {
                $movesSpend += $terrainCosts[$type][$fields[$step['y']][$step['x']]];
            }

            $movesLeft = $soldier['movesLeft'] - $movesSpend;
            if ($movesLeft < 0) {
                $movesLeft = 0;
            }

            $mSoldier->updateMovesLeft($movesLeft, $soldier['soldierId']);
        }

        $end = end($path);
        $data = array(
            'x' => $end['x'],
            'y' => $end['y'],
            'fortified' => 'false'
        );
        $where = array(
            $db->quoteInto('"armyId" = ?', $army['armyId']),
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId)
        );

        return self::update('army', $data, $where, $db);
    }

    static public function getEnemyArmiesFieldsPositions($gameId, $playerId, $db)
    {
        $fields = Zend_Registry::get('fields');

        $select = $db->select()
            ->from('army', array('x', 'y'))
            ->where('"gameId" = ?', $gameId)
            ->where('"playerId" != ?', $playerId)
            ->where('destroyed = false');

        try {
            $result = $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
            return;
        }

        foreach ($result as $row) {
            $fields[$row['y']][$row['x']] = 'e';
        }

        return $fields;
    }

    static public function getArmy($gameId, $armyId, $playerId, $db)
    {
        $select = $db->select()
            ->from('army', Cli_Model_Army::armyArray())
            ->where('"gameId" = ?', $gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false')
            ->where('"armyId" = ?', $armyId);

        try {
            $result = $db->fetchRow($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());

            return;
        }

        if (isset($result['armyId'])) {
            $mSoldier = new Application_Model_Soldier($gameId, $db);
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

            $result['heroes'] = $mHeroesInGame->getArmyHeroes($armyId);

            foreach ($result['heroes'] as $k => $row) {
                $mInventory = new Application_Model_Inventory($row['heroId'], $gameId, $db);
                $result['heroes'][$k]['artifacts'] = $mInventory->getAll();
            }

            $result['soldiers'] = $mSoldier->getForWalk($armyId);
            $result['movesLeft'] = self::calculateArmyMovesLeft($gameId, $armyId, $db);
        }

        return $result;
    }

    static public function getArmyByArmyIdPlayerId($gameId, $armyId, $playerId, $db)
    {
        $select = $db->select()
            ->from('army', Cli_Model_Army::armyArray())
            ->where('"gameId" = ?', $gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false')
            ->where('"armyId" = ?', $armyId);
        try {
            $result = $db->fetchRow($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());

            return;
        }

        if (isset($result['armyId'])) {
            $mSoldier = new Application_Model_Soldier($gameId, $db);
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
            $result['heroes'] = $mHeroesInGame->getArmyHeroes($armyId);
            foreach ($result['heroes'] as $k => $row) {
                $mInventory = new Application_Model_Inventory($row['heroId'], $gameId, $db);
                $result['heroes'][$k]['artifacts'] = $mInventory->getAll();
            }
            $result['soldiers'] = $mSoldier->getForWalk($armyId);
            $result['movesLeft'] = self::calculateArmyMovesLeft($gameId, $armyId, $db);

            return $result;
        }

    }

    static private function getArmyHeroesForBattle($gameId, $ids, $db)
    {
        $select = $db->select()
            ->from(array('a' => 'hero'), array('attackPoints', 'defensePoints', 'name', 'heroId'))
            ->join(array('b' => 'heroesingame'), 'a."heroId" = b."heroId"', '')
            ->where('"gameId" = ?', $gameId)
            ->where('"armyId" IN (?)', $ids)
            ->order('attackPoints DESC', 'defensePoints DESC', 'numberOfMoves DESC');

        try {
            $result = $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());

            return;
        }

        foreach ($result as $k => $row) {
            $mInventory = new Application_Model_Inventory($row['heroId'], $gameId, $db);
            $result[$k]['artefacts'] = $mInventory->getAll();
//            $result[$k]['artefacts'] = self::getArtefactsByHeroId($gameId, $row['heroId'], $db);
        }

        return $result;
    }

    static private function getArmyHeroes($gameId, $armyId, $in, $db)
    {
        $select = $db->select()
            ->from(array('a' => 'hero'), array('heroId', 'name', 'numberOfMoves', 'attackPoints', 'defensePoints'))
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
            foreach ($result as $k => $row) {
                $mInventory = new Application_Model_Inventory($row['heroId'], $gameId, $db);
                $result[$k]['artefacts'] = $mInventory->getAll();
//                $result[$k]['artefacts'] = self::getArtefactsByHeroId($gameId, $row['heroId'], $db);
            }

            return $result;
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static private function getArmySoldiers($gameId, $armyId, $db)
    {
        $select = $db->select()
            ->from('soldier', 'unitId')
            ->where('"gameId" = ?', $gameId)
            ->where('"armyId" = ?', $armyId);

        try {
            return $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function calculateArmyMovesLeft($gameId, $armyId, $db)
    {
        $heroMovesLeft = self::getMinHeroesMovesLeft($gameId, $armyId, $db);
        $soldierMovesLeft = self::getMinSoldiersMovesLeft($gameId, $armyId, $db);

        if ($soldierMovesLeft && $heroMovesLeft) {
            if ($heroMovesLeft > $soldierMovesLeft) {
                return $soldierMovesLeft;
            } else {
                return $heroMovesLeft;
            }
        } elseif ($soldierMovesLeft === null) {
            return (int)$heroMovesLeft;
        } elseif ($heroMovesLeft === null) {
            return (int)$soldierMovesLeft;
        }

        return 0;
    }

    static private function getMinHeroesMovesLeft($gameId, $armyId, $db)
    {
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

    static private function getMinSoldiersMovesLeft($gameId, $armyId, $db)
    {
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

//    static public function getInGameWSSUIdsExceptMine($gameId, $playerId, $db)
//    {
//        $select = $db->select()
//            ->from('playersingame', 'webSocketServerUserId')
//            ->where('"gameId" = ?', $gameId)
//            ->where('"playerId" != ?', $playerId);
//
//        try {
//            return $db->query($select)->fetchAll();
//        } catch (Exception $e) {
//            echo($e);
//            echo($select->__toString());
//        }
//    }

    static public function isPlayerTurn($gameId, $playerId, $db)
    {
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

    static public function getPlayerArmies($gameId, $playerId, $db)
    {
        $select = $db->select()
            ->from('army', Cli_Model_Army::armyArray())
            ->where('"gameId" = ?', $gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false');

        try {
            $result = $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());

            return;
        }

        $armies = array();

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mSoldier = new Application_Model_Soldier($gameId, $db);

        foreach ($result as $army) {
            $armies['army' . $army['armyId']] = $army;
            $armies['army' . $army['armyId']]['heroes'] = $mHeroesInGame->getArmyHeroes($army['armyId']);
            foreach ($armies['army' . $army['armyId']]['heroes'] as $k => $row) {
                $mInventory = new Application_Model_Inventory($row['heroId'], $gameId, $db);
                $armies['army' . $army['armyId']]['heroes'][$k]['artifacts'] = $mInventory->getAll();
            }

            $armies['army' . $army['armyId']]['soldiers'] = $mSoldier->getForWalk($army['armyId']);
            if (empty($armies['army' . $army['armyId']]['heroes']) AND empty($armies['army' . $army['armyId']]['soldiers'])) {
                self::destroyArmy($gameId, $armies['army' . $army['armyId']]['armyId'], $playerId, $db);
                unset($armies['army' . $army['armyId']]);
            }
        }

        return $armies;
    }

    static public function getArmyByArmyId($gameId, $armyId, $db)
    {
        $select = $db->select()
            ->from('army', Cli_Model_Army::armyArray('playerId'))
            ->where('"gameId" = ?', $gameId)
            ->where('"armyId" = ?', $armyId);

        try {
            $result = $db->fetchRow($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }

        if ($result['destroyed']) {
            $result['heroes'] = array();
            $result['soldiers'] = array();

            return $result;
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $result['heroes'] = $mHeroesInGame->getArmyHeroes($result['armyId']);
        foreach ($result['heroes'] as $k => $row) {
            $mInventory = new Application_Model_Inventory($row['heroId'], $gameId, $db);
            $result['heroes'][$k]['artifacts'] = $mInventory->getAll();
        }

        $mSoldier = new Application_Model_Soldier($gameId, $db);
        $result['soldiers'] = $mSoldier->getForWalk($result['armyId']);

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
    }

    static public function destroyArmy($gameId, $armyId, $playerId, $db)
    {
        $data = array(
            'destroyed' => 'true'
        );
        $where = array(
            $db->quoteInto('"armyId" = ?', $armyId),
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId)
        );

        return self::update('army', $data, $where, $db);
    }

    static public function isEnemyCastle($gameId, $castleId, $playerId, $db)
    {

        $select = $db->select()
            ->from('castlesingame', 'castleId')
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

    static public function getAllEnemyUnitsFromCastlePosition($gameId, $position, $db)
    {
        $xs = array(
            $position['x'],
            $position['x'] + 1
        );
        $ys = array(
            $position['y'],
            $position['y'] + 1
        );
        $ids = array();
        $select = $db->select()
            ->from('army', 'armyId')
            ->where('"gameId" = ?', $gameId)
            ->where('destroyed = false')
            ->where('x IN (?)', $xs)
            ->where('y IN (?)', $ys);
        try {
            $result = $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }

        foreach ($result as $id) {
            $ids[] = $id['armyId'];
        }
        if ($ids) {
            $mSoldier = new Application_Model_Soldier($gameId, $db);
            return array(
                'heroes' => self::getArmyHeroesForBattle($gameId, $ids, $db),
                'soldiers' => $mSoldier->getForBattle($ids),
                'ids' => $ids
            );
        } else {
            return array(
                'heroes' => array(),
                'soldiers' => array(),
                'ids' => array()
            );
        }
    }

    static public function getDefender($gameId, $ids, $db)
    {
        if (empty($ids)) {
            return;
        }

        $select = $db->select()
            ->from('army', Cli_Model_Army::armyArray('playerId'))
            ->where('"gameId" = ?', $gameId)
            ->where('"armyId" IN (?)', new Zend_Db_Expr(implode(',', $ids)));

        try {
            $result = $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }

        foreach ($result as $k => $army) {
            unset($result[$k]['playerId']);

            if ($army['destroyed']) {
                continue;
            }

            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
            $heroes = $mHeroesInGame->getArmyHeroes($army['armyId']);
//            foreach ($heroes as $k => $row) {
//                $mInventory = new Application_Model_Inventory($row['heroId'], $gameId, $db);
//                $heroes[$k]['artifacts'] = $mInventory->getAll();
//            }

            $mSoldier = new Application_Model_Soldier($gameId, $db);
            $soldiers = $mSoldier->getSoldiers($army['armyId']);

            if (empty($heroes) AND empty($soldiers)) {
                self::destroyArmy($gameId, $army['armyId'], $army['playerId'], $db);
                $result[$k]['destroyed'] = true;
            } else {
                $result[$k]['heroes'] = $heroes;
                $result[$k]['soldiers'] = $soldiers;
            }
        }

        return $result;
    }

    static public function getAllEnemyUnitsFromPosition($gameId, $position, $playerId, $db)
    {
        $select = $db->select()
            ->from('army', 'armyId')
            ->where('"gameId" = ?', $gameId)
            ->where('"playerId" != ?', $playerId)
            ->where('destroyed = false')
            ->where('x = (?)', $position['x'])
            ->where('y = (?)', $position['y']);

        try {
            $result = $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());

            return;
        }

        $ids = array();

        foreach ($result as $id) {
            $ids[] = $id['armyId'];
        }

        if ($ids) {
            $mSoldier = new Application_Model_Soldier($gameId, $db);
            return array(
                'heroes' => self::getArmyHeroesForBattle($gameId, $ids, $db),
                'soldiers' => $mSoldier->getForBattle($ids),
                'ids' => $ids
            );
        } else {
            return array(
                'heroes' => null,
                'soldiers' => null,
                'ids' => null
            );
        }
    }

    static public function areMySwimmingUnitsAtPosition($gameId, $position, $playerId, $db)
    {
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
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }

        foreach ($result as $id) {
            if ($ids) {
                $ids .= ',';
            }
            $ids .= $id['armyId'];
        }

        if (!$ids) {
            return;
        }

        $mSoldier = new Application_Model_Soldier($gameId, $db);

        return $mSoldier->getSwimmingFromArmiesIds($ids);
    }

    static public function getDefenderFromPosition($gameId, $position, $db)
    {
        $select = $db->select()
            ->from('army', Cli_Model_Army::armyArray('playerId'))
            ->where('"gameId" = ?', $gameId)
            ->where('destroyed = false')
            ->where('x = ?', $position['x'])
            ->where('y = ?', $position['y']);
        try {
            $result = $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mSoldier = new Application_Model_Soldier($gameId, $db);

        foreach ($result as $k => $army) {
            $heroes = $mHeroesInGame->getArmyHeroes($army['armyId']);
            foreach ($heroes as $k => $row) {
                $mInventory = new Application_Model_Inventory($row['heroId'], $gameId, $db);
                $heroes[$k]['artifacts'] = $mInventory->getAll();
            }
            $soldiers = $mSoldier->getSoldiers($army['armyId']);
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
    }

    static public function zeroHeroMovesLeft($gameId, $armyId, $heroId, $playerId, $db)
    {

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

        return self::update('heroesingame', $data, $where, $db);
    }

    static public function getHeroIdByPlayerId($gameId, $playerId, $db)
    {

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

    static public function getHeroIdByArmyIdPlayerId($gameId, $armyId, $playerId, $db)
    {

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

    static public function getArmyPositionByArmyId($gameId, $armyId, $playerId, $db)
    {
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

    static public function isHeroInArmy($gameId, $armyId, $playerId, $heroId, $db)
    {
        $select = $db->select()
            ->from(array('a' => 'hero'), 'heroId')
            ->join(array('b' => 'heroesingame'), 'a."heroId" = b."heroId"')
            ->where('"gameId" = ?', $gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('"armyId" = ?', $armyId)
            ->where('a."heroId" = ?', $heroId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function isSoldierInArmy($gameId, $armyId, $playerId, $soldierId, $db)
    {
        $select = $db->select()
            ->from(array('a' => 'soldier'), 'soldierId')
            ->join(array('b' => 'army'), 'a."armyId"=b."armyId"', '')
            ->where('a."gameId" = ?', $gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('a."armyId" = ?', $armyId)
            ->where('"soldierId" = ?', $soldierId)
            ->where('destroyed = false');
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static private function getNewArmyId($gameId, $db)
    {

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

    static public function heroUpdateArmyId($gameId, $heroId, $newArmyId, $db)
    {
        $data = array(
            'armyId' => $newArmyId
        );
        $where = array(
            $db->quoteInto('"heroId" = ?', $heroId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );

        return self::update('heroesingame', $data, $where, $db);
    }

    static public function soldierUpdateArmyId($gameId, $soldierId, $newArmyId, $db)
    {
        $data = array(
            'armyId' => $newArmyId
        );
        $where = array(
            $db->quoteInto('"soldierId" = ?', $soldierId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );

        return self::update('soldier', $data, $where, $db);
    }

    static public function isHeroInGame($gameId, $playerId, $db)
    {
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

    static public function connectHero($gameId, $playerId, $db)
    {

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

    static public function heroResurrection($gameId, $heroId, $position, $playerId, $db)
    {

        $armyId = self::getArmyIdFromPosition($gameId, $position, $db);
        if (!$armyId) {
            $mArmy = new Application_Model_Army($gameId, $db);
            $armyId = $mArmy->createArmy($position, $playerId);
        }
        self::addHeroToArmy($gameId, $armyId, $heroId, 0, $db);

        return $armyId;
    }

    static public function getArmyIdFromPosition($gameId, $position, $db)
    {

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

    static public function addHeroToArmy($gameId, $armyId, $heroId, $movesLeft, $db)
    {

        $data = array(
            'armyId' => $armyId,
            'movesLeft' => $movesLeft
        );
        $where = array(
            $db->quoteInto('"heroId" = ?', $heroId),
            $db->quoteInto('"gameId" = ?', $gameId)
        );

        return self::update('heroesingame', $data, $where, $db);
    }

    static public function areUnitsAtCastlePosition($gameId, $position, $db)
    {
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

    static public function isGameMaster($gameId, $playerId, $db)
    {

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

    static public function getAllPlayerArmiesExeptOne($gameId, $armyId, $playerId, $db)
    {

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

    static public function getTurnPlayerId($gameId, $db)
    {

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

    static public function isComputer($playerId, $db)
    {
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

    static public function resetHeroesMovesLeft($gameId, $playerId, $db)
    {
        $select = $db->select()
            ->from(array('a' => 'heroesingame'), array('movesLeft', 'heroId'))
            ->join(array('b' => 'hero'), 'a."heroId"=b."heroId"', '')
            ->where('"playerId" = ?', $playerId)
            ->where('a."gameId" = ?', $gameId);

        try {
            $heroesingame = $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());

            return;
        }

        foreach ($heroesingame as $hero) {
            if ($hero['movesLeft'] > 2) {
                $hero['movesLeft'] = 2;
            }
            $select = $db->select()
                ->from('hero', new Zend_Db_Expr('"numberOfMoves" + ' . $hero['movesLeft']))
                ->where('"playerId" = ?', $playerId)
                ->where('"heroId" = ?', $hero['heroId']);
            $data = array(
                'movesLeft' => new Zend_Db_Expr('(' . $select->__toString() . ')')
            );
            $where = array(
                $db->quoteInto('"heroId" = ?', $hero['heroId']),
                $db->quoteInto('"gameId" = ?', $gameId)
            );
            self::update('heroesingame', $data, $where, $db);
        }
    }

    static public function getComputerArmyToMove($gameId, $playerId, $db)
    {
        $select = $db->select()
            ->from('army', Cli_Model_Army::armyArray())
            ->where('"gameId" = ?', $gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false')
            ->where('fortified = false');

        try {
            $result = $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());

            return;
        }

        $mSoldier = new Application_Model_Soldier($gameId, $db);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

        foreach ($result as $army) {
            $army['heroes'] = $mHeroesInGame->getArmyHeroes($army['armyId']);
            foreach ($army['heroes'] as $k => $row) {
                $mInventory = new Application_Model_Inventory($row['heroId'], $gameId, $db);
                $army['heroes'][$k]['artifacts'] = $mInventory->getAll();
            }
            $army['soldiers'] = $mSoldier->getForWalk($army['armyId']);
            if (empty($army['heroes']) AND empty($army['soldiers'])) {
                self::destroyArmy($gameId, $army['armyId'], $playerId, $db);
            }
            $army['movesLeft'] = self::calculateArmyMovesLeft($gameId, $army['armyId'], $db);

            return $army;
        }
    }

    static public function getTurnNumber($gameId, $db)
    {

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

    static public function getPlayerCastlesIds($gameId, $playerId, $db)
    {
        $select = $db->select()
            ->from('castlesingame', 'castleId')
            ->where('"playerId" = ?', $playerId)
            ->where('"gameId" = ?', $gameId)
            ->where('razed = false');
        try {
            return $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getAllCastles($gameId, $db)
    {

        $castles = array();
        $select = $db->select()
            ->from('castlesingame')
            ->where('"gameId" = ?', $gameId);
        try {
            foreach ($db->query($select)->fetchAll() as $val) {
                $castles[$val['castleId']] = $val;
            }

            return $castles;
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function enemiesCastlesExist($gameId, $playerId, $db)
    {

        $select = $db->select()
            ->from('castlesingame', 'castleId')
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

    static public function getRazedCastles($gameId, $db)
    {

        $castles = array();
        $select = $db->select()
            ->from('castlesingame')
            ->where('"gameId" = ?', $gameId)
            ->where('razed = true');
        try {
            $result = $db->query($select)->fetchAll();
            foreach ($result as $val) {
                $castles[$val['castleId']] = $val;
            }

            return $castles;
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getAllEnemiesArmies($gameId, $playerId, $db)
    {
        $select = $db->select()
            ->from('army', Cli_Model_Army::armyArray())
            ->where('"gameId" = ?', $gameId)
            ->where('"playerId" != ?', $playerId)
            ->where('destroyed = false');
        try {
            $result = $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }

        $armies = array();

        $mSoldier = new Application_Model_Soldier($gameId, $db);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

        foreach ($result as $army) {
            $armies['army' . $army['armyId']] = $army;
            $armies['army' . $army['armyId']]['heroes'] = $mHeroesInGame->getArmyHeroes($army['armyId']);

            foreach ($armies['army' . $army['armyId']]['heroes'] as $k => $row) {
                $mInventory = new Application_Model_Inventory($row['heroId'], $gameId, $db);
                $armies['army' . $army['armyId']]['heroes'][$k]['artifacts'] = $mInventory->getAll();
            }

            $armies['army' . $army['armyId']]['soldiers'] = $mSoldier->getForWalk($army['armyId']);
            if (empty($armies['army' . $army['armyId']]['heroes']) AND empty($armies['army' . $army['armyId']]['soldiers'])) {
                self::destroyArmy($gameId, $armies['army' . $army['armyId']]['armyId'], $playerId, $db);
                unset($armies['army' . $army['armyId']]);
            } else {
                $armies['army' . $army['armyId']]['movesLeft'] = self::calculateMaxArmyMoves($gameId, $army['armyId'], $db);
            }
        }

        return $armies;
    }

    static public function calculateMaxArmyMoves($gameId, $armyId, $db)
    {
        $heroMoves = self::getMaxHeroesMoves($gameId, $armyId, $db);

        $mSoldier = new Application_Model_Soldier($gameId, $db);
        $soldierMoves = $mSoldier->getMaximumMoves($armyId);
        if ($heroMoves > $soldierMoves) {
            return $heroMoves;
        } else {
            return $soldierMoves;
        }
    }

    static private function getMaxHeroesMoves($gameId, $armyId, $db)
    {

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

    static public function playerCastlesExists($gameId, $playerId, $db)
    {

        $select = $db->select()
            ->from('castlesingame', 'castleId')
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

    static public function playerArmiesExists($gameId, $playerId, $db)
    {

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

    static public function endGame($gameId, $db)
    {
        $data['isActive'] = 'false';

        self::updateGame($gameId, $data, $db);
    }

    static public function updateGame($gameId, $data, $db)
    {

        $where = $db->quoteInto('"gameId" = ?', $gameId);

        return self::update('game', $data, $where, $db);
    }

    static private function isFirstColor($color)
    {
        $playerColors = Zend_Registry::get('colors');
        return $playerColors[0] == $color;
    }

    static public function updateTurnNumber($gameId, $nextPlayer, $db)
    {
        if (self::isFirstColor($nextPlayer['color'])) {
            $select = $db->select()
                ->from('game', array('turnNumber' => '("turnNumber" + 1)'))
                ->where('"gameId" = ?', $gameId);
            try {
                $turnNumber = $db->fetchOne($select);
            } catch (Exception $e) {
                echo($e);
                echo($select->__toString());

                return;
            }
            $data = array(
                'turnNumber' => $turnNumber,
                'end' => new Zend_Db_Expr('now()'),
                'turnPlayerId' => $nextPlayer['playerId']
            );
        } else {
            $data = array(
                'turnPlayerId' => $nextPlayer['playerId']
            );
        }

        self::updateGame($gameId, $data, $db);
    }

    static public function raiseAllCastlesProductionTurn($gameId, $playerId, $db)
    {
        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId)
        );
        $data = array(
            'productionTurn' => new Zend_Db_Expr('"productionTurn" + 1')
        );

        return self::update('castlesingame', $data, $where, $db, true);
    }

    static public function getColorByArmyId($gameId, $armyId, $db)
    {

        $select = $db->select()
            ->from('army', 'playerId')
            ->where('"gameId" = ?', $gameId)
            ->where('"armyId" = ?', $armyId);

        try {
            $playerId = $db->fetchOne($select);
            if ($playerId) {
                $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
                return $mPlayersInGame->getColorByPlayerId($playerId);
            } else {
                print_r(debug_backtrace(0, 2));
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getColorByCastleId($gameId, $castleId, $db)
    {

        $select = $db->select()
            ->from('castlesingame', 'playerId')
            ->where('"gameId" = ?', $gameId)
            ->where('"castleId" = ?', $castleId);
        try {
            $playerId = $db->fetchOne($select);
            if ($playerId) {
                $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
                return $mPlayersInGame->getColorByPlayerId($playerId);
            } else {
                print_r(debug_backtrace(0, 2));
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getGameMasterId($gameId, $db)
    {
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

    static public function isGameStarted($gameId, $db)
    {
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

    static public function unfortifyComputerArmies($gameId, $playerId, $db)
    {
        $data = array(
            'fortified' => 'false'
        );
        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId),
        );

        return self::update('army', $data, $where, $db, true);
    }

    static public function insertChatMessage($gameId, $playerId, $message, $db)
    {
        $data = array(
            'message' => $message,
            'playerId' => $playerId,
            'gameId' => $gameId
        );
        try {
            return $db->insert('chat', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    /**
     * @param Zend_Db_Adapter_Pdo_Pgsql $db
     * @param int $gameId
     * @param int $playerId
     * @param string $data
     * @return mixed
     */
    static public function addTokensIn(Zend_Db_Adapter_Pdo_Pgsql $db, $gameId, $playerId, $token)
    {
        $data = array(
            'playerId' => $playerId,
            'gameId' => $gameId,
            'type' => $token['type']
        );

        unset($token['type']);

        $data['data'] = Zend_Json::encode($token);

        try {
            return $db->insert('tokensin', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    /**
     * @param Zend_Db_Adapter_Pdo_Pgsql $db
     * @param int $gameId
     * @param int $playerId
     * @param string $data
     * @return mixed
     */
    static public function addTokensOut(Zend_Db_Adapter_Pdo_Pgsql $db, $gameId, $token)
    {
        $data = array(
            'gameId' => $gameId,
            'type' => $token['type']
        );

        unset($token['type']);

        $keys = array(
            'attackerColor',
            'attackerArmy',
            'defenderColor',
            'defenderArmy',
            'path',
            'battle',
            'oldArmyId',
            'deletedIds',
            'victory',
            'castleId',
            'ruinId',
            'lost',
            'win',
            'gold',
            'costs',
            'income',
            'armies',
            'nr',
            'action',
            'color',
            'x',
            'y',
        );

        foreach ($keys as $key) {
            self::prepareGameHistoryData($key, $data, $token);
        }

        $data['data'] = Zend_Json::encode($token);

        try {
            return $db->insert('tokensout', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }


    static public function prepareGameHistoryData($value, &$data, &$token)
    {
        if (array_key_exists($value, $token)) {
            if (is_array($token[$value])) {
                $data[$value] = Zend_Json::encode($token[$value]);
            } elseif (is_bool($token[$value])) {
                if ($token[$value]) {
                    $data[$value] = 't';
                } else {
                    $data[$value] = 'f';
                }
            } else {
                $data[$value] = $token[$value];
            }

            unset($token[$value]);
        }
    }

    static public function isOtherArmyAtPosition($gameId, $armyId, $x, $y, $db)
    {
        $select = $db->select()
            ->from('army', 'armyId')
            ->where('"gameId" = ?', $gameId)
            ->where('"armyId" != ?', $armyId)
            ->where('destroyed = false')
            ->where('x = ?', $x)
            ->where('y = ?', $y);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }
}
