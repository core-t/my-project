<?php

class Cli_Model_ComputerSubBlocks
{

    static public function fightEnemy($gameId, $army, $path, $fields, $enemy, $playerId, $castleId, $db)
    {
        $result = array(
            'victory' => false
        );

        $position = end($path);
        $fields = Application_Model_Board::changeArmyField($fields, $position['x'], $position['y'], 'E');
        $mapCastles = Zend_Registry::get('castles');
        $mArmy2 = new Application_Model_Army($gameId, $db);

        if ($castleId !== null) { // castle
            $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);

            if ($mCastlesInGame->isEnemyCastle($castleId, $playerId)) { // enemy castle
                $playersInGameColors = Zend_Registry::get('playersInGameColors');
                $defenderId = $mCastlesInGame->getPlayerIdByCastleId($castleId);
                $result['defenderColor'] = $playersInGameColors[$defenderId];
                $enemy = Cli_Model_Army::getCastleGarrisonFromCastlePosition($mapCastles[$castleId]['position'], $gameId, $db);
                $enemy = Cli_Model_Army::addCastleDefenseModifier($enemy, $gameId, $castleId, $db);
                $battle = new Cli_Model_Battle($army, $enemy);
                $battle->fight();
                $battle->updateArmies($gameId, $db, $playerId, $defenderId);
                $defender = $mArmy2->getDefender($enemy['ids']);

                if (!$battle->getDefender()) {
                    Cli_Model_Army::updateArmyPosition($playerId, $path, $fields, $army, $gameId, $db);
                    $result['attackerArmy'] = Cli_Model_Army::getArmyByArmyIdPlayerId($army['armyId'], $playerId, $gameId, $db);
                    $result['victory'] = true;
                    $mCastlesInGame->changeOwner($mapCastles[$castleId], $playerId);
                } else {
                    $result['attackerArmy'] = array(
                        'armyId' => $army['armyId'],
                        'destroyed' => true
                    );
                    $mArmy2->destroyArmy($army['armyId'], $playerId);
                }
            } else { // neutral castle
                $enemy = Cli_Model_Battle::getNeutralCastleGarrison($gameId, $db);
                $battle = new Cli_Model_Battle($army, $enemy);
//                $battle->setCombatAttackModifiers($army);
                $battle->fight();
                $battle->updateArmies($gameId, $db, $playerId, 0);
                $defender = $battle->getDefender();

                if (!$battle->getDefender()) {
                    Cli_Model_Army::updateArmyPosition($playerId, $path, $fields, $army, $gameId, $db);
                    $result['attackerArmy'] = Cli_Model_Army::getArmyByArmyIdPlayerId($army['armyId'], $playerId, $gameId, $db);

                    $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
                    $mCastlesInGame->addCastle($castleId, $playerId);
                    $result['victory'] = true;
                } else {
                    $result['attackerArmy'] = array(
                        'armyId' => $army['armyId'],
                        'destroyed' => true
                    );
                    $mArmy2->destroyArmy($army['armyId'], $playerId);
                    $defender = null;
                }
                $result['defenderColor'] = 'neutral';
            }
        } else { // enemy army
            $enemy = Cli_Model_Army::setCombatDefenseModifiers($enemy);
            $enemy = Cli_Model_Army::addTowerDefenseModifier($enemy);
            $enemy['ids'][] = $enemy['armyId'];
            $battle = new Cli_Model_Battle($army, $enemy);
            $battle->fight();
            $defenderId = $mArmy2->getPlayerIdFromPosition($playerId, $enemy);
            $battle->updateArmies($gameId, $db, $playerId, $defenderId);
            $defender = $mArmy2->getDefender($enemy['ids']);

            if (!$battle->getDefender()) {
                Cli_Model_Army::updateArmyPosition($playerId, $path, $fields, $army, $gameId, $db);
                $result['attackerArmy'] = Cli_Model_Army::getArmyByArmyIdPlayerId($army['armyId'], $playerId, $gameId, $db);
                $result['victory'] = true;
                $defender[0]['armyId'] = $enemy['armyId'];
            } else {
                $result['attackerArmy'] = array(
                    'armyId' => $army['armyId'],
                    'destroyed' => true
                );
                $mArmy2->destroyArmy($army['armyId'], $playerId);
            }
            $playersInGameColors = Zend_Registry::get('playersInGameColors');
            $result['defenderColor'] = $playersInGameColors[$defenderId];
        }

        $result['defenderArmy'] = $defender;
        $result['battle'] = $battle->getResult($army, $enemy);

        return $result;
    }

    static public function isEnemyStronger($gameId, $playerId, $db, $army, $enemy, $castleId, $max = 30)
    {
        $attackerWinsCount = 0;
        $attackerCourage = 2;

        $enemy = Cli_Model_Army::setCombatDefenseModifiers($enemy);
        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);

        if ($castleId !== null && $mCastlesInGame->isEnemyCastle($castleId, $playerId)) {
            $enemy = Cli_Model_Army::addCastleDefenseModifier($enemy, $gameId, $castleId, $db);
        } else {
            $enemy = Cli_Model_Army::addTowerDefenseModifier($enemy);
        }

        for ($i = 0; $i < $max; $i++) {
            $battle = new Cli_Model_Battle($army, $enemy);
            $battle->fight();
            if ($battle->getAttacker()) {
                $attackerWinsCount++;
            }
        }

        $border = $max - $attackerWinsCount - $attackerCourage;
        if ($attackerWinsCount >= $border) {
            return false;
        } else {
            return true;
        }
    }

    static public function getWeakerEnemyCastle($gameId, $castles, $army, $playerId, $db)
    {
        $heuristics = array();
        foreach ($castles as $castleId => $castle) {
            $mHeuristics = new Cli_Model_Heuristics($castle['position']['x'], $castle['position']['y']);
            $heuristics[$castleId] = $mHeuristics->calculateH($army['x'], $army['y']);
        }
        asort($heuristics, SORT_NUMERIC);

        $mArmy2 = new Application_Model_Army($gameId, $db);

        foreach (array_keys($heuristics) as $castleId) {
            $position = $castles[$castleId]['position'];
            $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
            if ($mCastlesInGame->isEnemyCastle($castleId, $playerId)) {
                $enemy = Cli_Model_Army::getCastleGarrisonFromCastlePosition($position, $gameId, $db);
            } else {
                $enemy = Cli_Model_Battle::getNeutralCastleGarrison($gameId, $db);
            }
            $enemy = array_merge($enemy, $position);

            if (!self::isEnemyStronger($gameId, $playerId, $db, $army, $enemy, $castleId)) {
                return $castleId;
            }
        }
        return null;
    }

    static public function isEnemyCastleInRange($castlesAndFields, $castleId, $mArmy)
    {
        $mapCastles = Zend_Registry::get('castles');
        $army = $mArmy->getArmy();
        $position = $mapCastles[$castleId]['position'];
        $fields = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $position['x'], $position['y'], 'E');
        try {
            $aStar = new Cli_Model_Astar($army, $position['x'], $position['y'], $fields);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $move = $mArmy->calculateMovesSpend($aStar->getPath($position['x'] . '_' . $position['y']));
        if (Application_Model_Board::isCastleField($move['currentPosition'], $position)) {
            $move['in'] = true;
        } else {
            $move['in'] = false;
        }



        return $move;
    }

    static public function isEnemyArmyInRange($castlesAndFields, $enemy, $mArmy)
    {
        $army = $mArmy->getArmy();
        $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $castlesAndFields['hostileCastles']);
        if ($castleId !== null) {
            $castlesAndFields['fields'] = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'E');
        } else {
            $castlesAndFields['fields'] = Application_Model_Board::restoreField($castlesAndFields['fields'], $enemy['x'], $enemy['y']);
        }
        try {
            $aStar = new Cli_Model_Astar($army, $enemy['x'], $enemy['y'], $castlesAndFields['fields']);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $move = $mArmy->calculateMovesSpend($aStar->getPath($enemy['x'] . '_' . $enemy['y']));
        if ($move['currentPosition']['x'] == $enemy['x'] && $move['currentPosition']['y'] == $enemy['y']) {
            $move['in'] = true;
        } else {
            $move['in'] = false;
        }
        $move['castleId'] = $castleId;
        return $move;
    }

    static public function getEnemiesHaveRangeAtThisCastle($castlePosition, $castlesAndFields, $enemies)
    {
        $enemiesHaveRange = array();
        foreach ($enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($castlePosition['x'], $castlePosition['y']);
            $h = $mHeuristics->calculateH($enemy['x'], $enemy['y']);
            if ($h < ($enemy['movesLeft'])) {
                $mArmy = new Cli_Model_Army($enemy);
                $enemy = $mArmy->getArmy();

                $castlesAndFields['fields'] = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $castlePosition['x'], $castlePosition['y'], 'E');

                try {
                    $aStar = new Cli_Model_Astar($enemy, $castlePosition['x'], $castlePosition['y'], $castlesAndFields['fields']);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $castlesAndFields['fields'] = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $castlePosition['x'], $castlePosition['y'], 'e');

                if ($mArmy->unitsHaveRange($aStar->getPath($castlePosition['x'] . '_' . $castlePosition['y']))) {
                    $enemiesHaveRange[] = $enemy;
                }
            }
        }
        if (!empty($enemiesHaveRange)) {
            return $enemiesHaveRange;
        } else {
            return false;
        }
    }

    static public function getEnemiesInRange($enemies, $mArmy, $fields)
    {
        $army = $mArmy->getArmy();
        $enemiesInRange = array();
        foreach ($enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($army['x'], $army['y']);
            $h = $mHeuristics->calculateH($enemy['x'], $enemy['y']);
            if ($h < $army['movesLeft']) {
                $fields = Application_Model_Board::restoreField($fields, $enemy['x'], $enemy['y']);
                try {
                    $aStar = new Cli_Model_Astar($army, $enemy['x'], $enemy['y'], $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $fields = Application_Model_Board::changeArmyField($fields, $enemy['x'], $enemy['y'], 'e');

                $move = $mArmy->calculateMovesSpend($aStar->getPath($enemy['x'] . '_' . $enemy['y']));
                if ($move['currentPosition']['x'] == $enemy['x'] && $move['currentPosition']['y'] == $enemy['y']) {
//                    $enemy['aStar'] = $aStar;
//                    $enemy['key'] = $enemy['x'] . '_' . $enemy['y'];
                    $enemiesInRange[] = $enemy;
                }
            }
        }
        if (!empty($enemiesInRange)) {
            return $enemiesInRange;
        } else {
//             new Game_Logger('BRAK WROGA W ZASIĘGU ARMII');
            return false;
        }
    }

    static public function getNearestRuin($fields, $ruins, $mArmy)
    {
        $army = $mArmy->getArmy();
        foreach ($ruins as $ruinId => $ruin) {
            $mHeuristics = new Cli_Model_Heuristics($ruin['x'], $ruin['y']);
            $h = $mHeuristics->calculateH($army['x'], $army['y']);
            if ($h < $army['movesLeft']) {
                try {
                    $aStar = new Cli_Model_Astar($army, $ruin['x'], $ruin['y'], $fields, array('limit' => true));
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $mArmy->calculateMovesSpend($aStar->getPath($ruin['x'] . '_' . $ruin['y']));
                if ($move['currentPosition']['x'] == $ruin['x'] && $move['currentPosition']['y'] == $ruin['y']) {
                    $ruin['ruinId'] = $ruinId;
                    return array_merge($ruin, $move);
                }
            }
        }
    }

    static public function getMyEmptyCastleInMyRange($gameId, $myCastles, $mArmy, $fields, $db)
    {
        $army = $mArmy->getArmy();
        $mArmy2 = new Application_Model_Army($gameId, $db);
        foreach ($myCastles as $castle) {
            $position = $castle['position'];
            if ($mArmy2->areUnitsAtCastlePosition($position)) {
                continue;
            }
            $mHeuristics = new Cli_Model_Heuristics($army['x'], $army['y']);
            $h = $mHeuristics->calculateH($position['x'], $position['y']);
            if ($h < $army['movesLeft']) {
//                $fields = Application_Model_Board::changeCasteFields($fields, $position['x'], $position['y'], 'E');
                try {
                    $aStar = new Cli_Model_Astar($army, $position['x'], $position['y'], $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $mArmy->calculateMovesSpend($aStar->getPath($position['x'] . '_' . $position['y']));
                if ($move['currentPosition']['x'] == $position['x'] && $move['currentPosition']['y'] == $position['y']) {
//                    $castle['movesSpend'] = $movesToSpend;
//                    $castle['path'] = $aStar->getPath($key, $army['movesLeft']);
//                    $castle['currentPosition'] = $aStar->getCurrentPosition();
                    $castle['x'] = $position['x'];
                    $castle['y'] = $position['y'];
                    return array_merge($castle, $move);
                }

//                $fields = Application_Model_Board::changeCasteFields($fields, $position['x'], $position['y'], 'e');
            }
        }
    }

    static public function isMyCastleInRangeOfEnemy($enemies, $myEmptyCastle, $fields)
    {
        foreach ($enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy['x'], $enemy['y']);
            $h = $mHeuristics->calculateH($myEmptyCastle['x'], $myEmptyCastle['y']);
            if ($h < $enemy['movesLeft']) {
                $fields = Application_Model_Board::changeCasteFields($fields, $myEmptyCastle['x'], $myEmptyCastle['y'], 'E');
                $mArmy = new Cli_Model_Army($enemy);
                $enemy = $mArmy->getArmy();
                try {
                    $aStar = new Cli_Model_Astar($enemy, $myEmptyCastle['x'], $myEmptyCastle['y'], $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                if ($mArmy->unitsHaveRange($aStar->getPath($myEmptyCastle['x'] . '_' . $myEmptyCastle['y']))) {
                    return true;
                }

                $fields = Application_Model_Board::changeCasteFields($fields, $myEmptyCastle['x'], $myEmptyCastle['y'], 'e');
            }
        }
    }

    static public function canAttackAllEnemyHaveRange($gameId, $playerId, $enemies, $army, $castles, $db = null)
    {
        foreach ($enemies as $enemy) {
            $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $castles);
            $enemy['castleId'] = $castleId;
            if (self::isEnemyStronger($gameId, $playerId, $db, $army, $enemy, $castleId)) {
                return null;
            }
        }
        return $enemy;
    }

    static public function getWeakerEnemyArmyInRange($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $db)
    {
        $army = $mArmy->getArmy();
        foreach ($enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy['x'], $enemy['y']);
            $h = $mHeuristics->calculateH($army['x'], $army['y']);
            if ($h < $army['movesLeft']) {
                $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $castlesAndFields['hostileCastles']);
                if (self::isEnemyStronger($gameId, $playerId, $db, $army, $enemy, $castleId)) {
                    continue;
                }
                if ($castleId !== null) {
                    $castlesAndFields['fields'] = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'E');
                } else {
                    $castlesAndFields['fields'] = Application_Model_Board::restoreField($castlesAndFields['fields'], $enemy['x'], $enemy['y']);
                }
                try {
                    $aStar = new Cli_Model_Astar($army, $enemy['x'], $enemy['y'], $castlesAndFields['fields']);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $mArmy->calculateMovesSpend($aStar->getPath($enemy['x'] . '_' . $enemy['y']));
                if ($move['currentPosition']['x'] == $enemy['x'] && $move['currentPosition']['y'] == $enemy['y']) {
                    $enemy['castleId'] = $castleId;
                    return array_merge($enemy, $move);
                }

                if ($castleId !== null) {
                    $castlesAndFields['fields'] = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'e');
                } else {
                    $castlesAndFields['fields'] = Application_Model_Board::changeArmyField($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'e');
                }
            }
        }
        return null;
    }

    static public function getStrongerEnemyArmyInRange($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $db)
    {
        $army = $mArmy->getArmy();
        foreach ($enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy['x'], $enemy['y']);
            $h = $mHeuristics->calculateH($army['x'], $army['y']);
            if ($h < $army['movesLeft']) {
                $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $castlesAndFields['hostileCastles']);
                if (!self::isEnemyStronger($gameId, $playerId, $db, $army, $enemy, $castleId)) {
                    continue;
                }
                if ($castleId !== null) {
                    $castlesAndFields['fields'] = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'E');
                } else {
                    $castlesAndFields['fields'] = Application_Model_Board::restoreField($castlesAndFields['fields'], $enemy['x'], $enemy['y']);
                }
                try {
                    $aStar = new Cli_Model_Astar($army, $enemy['x'], $enemy['y'], $castlesAndFields['fields']);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $mArmy->calculateMovesSpend($aStar->getPath($enemy['x'] . '_' . $enemy['y']));
                if ($move['currentPosition']['x'] == $enemy['x'] && $move['currentPosition']['y'] == $enemy['y']) {
//                    array_merge($enemy, $move);
//                    $enemy['castleId'] = $castleId;
                    return true;
                }
                if ($castleId !== null) {
                    $castlesAndFields['fields'] = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'e');
                } else {
                    $castlesAndFields['fields'] = Application_Model_Board::changeArmyField($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'e');
                }
            }
        }
        return null;
    }

    static public function getMyArmyInRange($gameId, $playerId, $mArmy, $fields, $db)
    {
        $army = $mArmy->getArmy();
        $mArmy2 = new Application_Model_Army($gameId, $db);
        $myArmies = $mArmy2->getAllPlayerArmiesExceptOne($army['armyId'], $playerId);
        foreach ($myArmies as $a) {
            $mHeuristics = new Cli_Model_Heuristics($a['x'], $a['y']);
            $h = $mHeuristics->calculateH($army['x'], $army['y']);
            if ($h < $army['movesLeft']) {
                try {
                    $aStar = new Cli_Model_Astar($army, $a['x'], $a['y'], $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $move = $mArmy->calculateMovesSpend($aStar->getPath($a['x'] . '_' . $a['y']));
                if ($move['currentPosition']['x'] == $a['x'] && $move['currentPosition']['y'] == $a['y']) {
                    return array_merge($a, $move);
                }
            }
        }
        return null;
    }

    static public function getMyCastleNearEnemy($enemies, $mArmy, $fields, $myCastles)
    {
        $army = $mArmy->getArmy();
        $heuristics = array();
        foreach ($enemies as $k => $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy['x'], $enemy['y']);
            $heuristics[$k] = $mHeuristics->calculateH($army['x'], $army['y']);
        }
        if (empty($heuristics)) {
            return null;
        }
        asort($heuristics, SORT_NUMERIC);
        $k = key($heuristics);
        $heuristics = array();
        foreach ($myCastles as $j => $castle) {
            $position = $castle['castleId']['position'];
            $mHeuristics = new Cli_Model_Heuristics($enemies[$k]['x'], $enemies[$k]['y']);
            $heuristics[$j] = $mHeuristics->calculateH($position['x'], $position['y']);
        }
        if (empty($heuristics)) {
            return null;
        }
        asort($heuristics, SORT_NUMERIC);
        $k = key($heuristics);
        $castle = $myCastles[$k];
        $position = $castle['castleId']['position'];
        try {
            $aStar = new Cli_Model_Astar($army, $position['x'], $position['y'], $fields);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $move = $mArmy->calculateMovesSpend($aStar->getPath($position['x'] . '_' . $position['y']));
        if ($move['currentPosition'] == $position) {
            return array_merge($castle, $move);
        } else {
            return null;
        }
    }

}

