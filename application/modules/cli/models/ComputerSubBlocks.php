<?php

class Cli_Model_ComputerSubBlocks
{

    static public function fightEnemy($gameId, $army, $path, $fields, $enemy, $playerId, $castleId, $db)
    {
        if (!$db) {
            $db = self::getDb();
        }
        $result = array(
            'victory' => false
        );

        if ($castleId !== null) { // castle
            if (Cli_Model_Database::isEnemyCastle($gameId, $castleId, $playerId, $db)) { // enemy castle
                $result['defenderColor'] = Cli_Model_Database::getColorByCastleId($gameId, $castleId, $db);
                $enemy = Cli_Model_Database::getAllEnemyUnitsFromCastlePosition($gameId, Application_Model_Board::getCastlePosition($castleId), $db);
                $enemy = Cli_Model_Army::addCastleDefenseModifier($enemy, $gameId, $castleId, $db);
                $battle = new Cli_Model_Battle($army, $enemy);
                $battle->fight();
                $battle->updateArmies($gameId, $db);
                $defender = Cli_Model_Database::getDefenderFromCastlePosition($gameId, Application_Model_Board::getCastlePosition($castleId), $db);

                if (empty($defender)) {
                    Cli_Model_Database::updateArmyPosition($gameId, $playerId, $path, $fields, $army, $db, true);
                    $result['attackerArmy'] = Cli_Model_Database::getArmyByArmyIdPlayerId($gameId, $army['armyId'], $playerId, $db);
                    $result['victory'] = true;
                    foreach ($enemy['ids'] as $id) {
                        $defender[]['armyId'] = $id;
                    }
                    echo('
Castle defender: ');
                    print_r($defender);
                    echo('
castleId: ' . $castleId);
                    Cli_Model_Database::changeOwner($gameId, $castleId, $playerId, $db);
                } else {
                    $result['attackerArmy'] = array(
                        'armyId' => $army['armyId'],
                        'destroyed' => true
                    );
                    Cli_Model_Database::destroyArmy($gameId, $army['armyId'], $playerId, $db);
                }
            } else { // neutral castle
                $enemy = Cli_Model_Battle::getNeutralCastleGarrison($gameId, $db);
                $battle = new Cli_Model_Battle($army, $enemy);
//                $battle->setCombatAttackModifiers($army);
                $battle->fight();
                $battle->updateArmies($gameId, $db);
                $defender = $battle->getDefender();

                if (empty($defender['soldiers'])) {
                    Cli_Model_Database::updateArmyPosition($gameId, $playerId, $path, $fields, $army, $db, true);
                    $result['attackerArmy'] = Cli_Model_Database::getArmyByArmyIdPlayerId($gameId, $army['armyId'], $playerId, $db);
                    Cli_Model_Database::addCastle($gameId, $castleId, $playerId, $db);
                    $result['victory'] = true;
                } else {
                    $result['attackerArmy'] = array(
                        'armyId' => $army['armyId'],
                        'destroyed' => true
                    );
                    Cli_Model_Database::destroyArmy($gameId, $army['armyId'], $playerId, $db);
                    $defender = null;
                }
                $result['defenderColor'] = 'neutral';
            }
        } else { // enemy army
            $enemy = Cli_Model_Army::setCombatDefenseModifiers($enemy);
            $enemy = Cli_Model_Army::addTowerDefenseModifier($enemy);
            $battle = new Cli_Model_Battle($army, $enemy);
            $battle->fight();
            $battle->updateArmies($gameId, $db);
            $defender = Cli_Model_Database::getDefenderFromPosition($gameId, array('x' => $enemy['x'], 'y' => $enemy['y']), $db);

            if (empty($defender)) {
                Cli_Model_Database::updateArmyPosition($gameId, $playerId, $path, $fields, $army, $db, true);
                $result['attackerArmy'] = Cli_Model_Database::getArmyByArmyIdPlayerId($gameId, $army['armyId'], $playerId, $db);
                $result['victory'] = true;
                $defender[0]['armyId'] = $enemy['armyId'];
            } else {
                $result['attackerArmy'] = array(
                    'armyId' => $army['armyId'],
                    'destroyed' => true
                );
                Cli_Model_Database::destroyArmy($gameId, $army['armyId'], $playerId, $db);
            }
            $result['defenderColor'] = Cli_Model_Database::getColorByArmyId($gameId, $enemy['armyId'], $db);
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
        if ($castleId !== null && Cli_Model_Database::isEnemyCastle($gameId, $castleId, $playerId, $db)) {
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
//         $weaker = array();

        foreach (array_keys($heuristics) as $castleId) {
            if (Cli_Model_Database::isEnemyCastle($gameId, $castleId, $playerId, $db)) {
                $enemy = Cli_Model_Database::getAllEnemyUnitsFromCastlePosition($gameId, Application_Model_Board::getCastlePosition($castleId), $db);
            } else {
                $enemy = Cli_Model_Battle::getNeutralCastleGarrison($gameId, $db);
            }
            if (!self::isEnemyStronger($gameId, $playerId, $db, $army, $enemy, $castleId)) {
//                 new Game_Logger('ENEMY SŁABSZY - 108');
                return $castleId;
            }
//             $weaker[$castleId] = Game_Battle::getCastlePower($castleId, $playerId);
        }
//         asort($weaker, SORT_NUMERIC);
        return null;
    }

    static public function isEnemyCastleInRange($castlesAndFields, $castleId, $mArmy)
    {
        $army = $mArmy->getArmy();
        $position = Application_Model_Board::getCastlePosition($castleId);
        $fields = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $position['x'], $position['y'], 'E');
        try {
            $aStar = new Cli_Model_Astar($army, $position['x'], $position['y'], $fields);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $move = $mArmy->calculateMovesSpend($aStar->getPath($position['x'] . '_' . $position['y']));
        if ($move['currentPosition']['x'] == $position['x'] && $move['currentPosition']['y'] == $position['y']) {
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

                $move = $mArmy->calculateMovesSpend($aStar->getPath($castlePosition['x'] . '_' . $castlePosition['y']));
                if ($move['currentPosition']['x'] == $castlePosition['x'] && $move['currentPosition']['y'] == $castlePosition['y']) {
//                    $enemy['aStar'] = $aStar;
//                    $enemy['key'] = $castlePosition['x'] . '_' . $castlePosition['y'];
//                    $enemy['movesToSpend'] = $movesToSpend + 2;
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
                    $aStar = new Cli_Model_Astar($army, $ruin['x'], $ruin['y'], $fields, true);
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
        foreach ($myCastles as $castle) {
            $position = Application_Model_Board::getCastlePosition($castle['castleId']);
            if (Cli_Model_Database::areUnitsAtCastlePosition($gameId, $position, $db)) {
                continue;
            }
            $mHeuristics = new Cli_Model_Heuristics($army['x'], $army['y']);
            $h = $mHeuristics->calculateH($position['x'], $position['y']);
            if ($h < $army['movesLeft']) {
                $fields = Application_Model_Board::changeCasteFields($fields, $position['x'], $position['y'], 'E');
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

                $fields = Application_Model_Board::changeCasteFields($fields, $position['x'], $position['y'], 'e');
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

                $move = $mArmy->calculateMovesSpend($aStar->getPath($myEmptyCastle['x'] . '_' . $myEmptyCastle['y']));
                if ($move['currentPosition']['x'] == $myEmptyCastle['x'] && $move['currentPosition']['y'] == $myEmptyCastle['y']) {
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
                    array_merge($enemy, $move);
                    $enemy['castleId'] = $castleId;
                    return $enemy;
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
        $myArmies = Cli_Model_Database::getAllPlayerArmiesExeptOne($gameId, $army['armyId'], $playerId, $db);
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
                    array_merge($a, $move);
                    return $a;
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
            $position = Application_Model_Board::getCastlePosition($castle['castleId']);
            $mHeuristics = new Cli_Model_Heuristics($enemies[$k]['x'], $enemies[$k]['y']);
            $heuristics[$j] = $mHeuristics->calculateH($position['x'], $position['y']);
        }
        if (empty($heuristics)) {
            return null;
        }
        asort($heuristics, SORT_NUMERIC);
        $k = key($heuristics);
        $castle = $myCastles[$k];
        $position = Application_Model_Board::getCastlePosition($castle['castleId']);
        try {
            $aStar = new Cli_Model_Astar($army, $position['x'], $position['y'], $fields);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $move = $mArmy->calculateMovesSpend($aStar->getPath($position['x'] . '_' . $position['y']));
        if ($move['currentPosition'] == $position) {
            array_merge($castle, $move);
            return $castle;
        } else {
            return null;
        }
    }

}

