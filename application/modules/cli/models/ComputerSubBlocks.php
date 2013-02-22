<?php

class Cli_Model_ComputerSubBlocks {

    static public function fightEnemy($gameId, $army, $path, $fields, $enemy, $playerId, $castleId, $db) {
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
                $battle = new Cli_Model_Battle($army, $enemy);
//                $battle->setCombatAttackModifiers($army);
//                $battle->setCombatDefenseModifiers($enemy);
                $battle->addCastleDefenseModifier($gameId, $castleId, $db);
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
                $enemy = Cli_Model_Battle::getNeutralCastleGarrizon($gameId, $db);
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
            $battle = new Cli_Model_Battle($army, $enemy);
//            $battle->setCombatAttackModifiers($army);
//            $battle->setCombatDefenseModifiers($enemy);
            $battle->addTowerDefenseModifier($enemy['x'], $enemy['y']);
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

    static public function isEnemyStronger($gameId, $playerId, $db, $army, $enemy, $castleId, $max = 30) {
        $attackerWinsCount = 0;
        $attackerCourage = 2;
        for ($i = 0; $i < $max; $i++) {
            $battle = new Cli_Model_Battle($army, $enemy);
//            $battle->setCombatAttackModifiers($army);
            if ($castleId !== null) {
                if (Cli_Model_Database::isEnemyCastle($gameId, $castleId, $playerId, $db)) {
                    $battle->addCastleDefenseModifier($gameId, $castleId, $db);
                }
            }
            if (isset($enemy['x']) && isset($enemy['y'])) {
//                $battle->setCombatDefenseModifiers($enemy);
                $battle->addTowerDefenseModifier($enemy['x'], $enemy['y']);
            }
            $battle->fight();
            if ($battle->getAttacker()) {
                $attackerWinsCount++;
            }
        }
        $border = $max - $attackerWinsCount - $attackerCourage;
//         new Game_Logger('attackerCount ' . $attackerCount . ' >= ' . $border);
        if ($attackerWinsCount >= $border) {
//            var_dump($attackerWinsCount . '=>' . $border);
//            var_dump('ENEMY SŁABSZY');
//             new Game_Logger('ENEMY SŁABSZY');
            return false;
        } else {
//            var_dump($attackerWinsCount . '<' . $border);
//            var_dump('ENEMY SILNIEJSZY');
//             new Game_Logger('ENEMY SILNIEJSZY');
            return true;
        }
    }

    static public function getWeakerEnemyCastle($gameId, $castles, $army, $playerId, $db = null) {
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
                $enemy = Cli_Model_Battle::getNeutralCastleGarrizon($gameId, $db);
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

    static public function isEnemyCastleInRange($castlesAndFields, $castleId, $army) {
        $position = Application_Model_Board::getCastlePosition($castleId);
        $fields = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $position['x'], $position['y'], 'c');
        try {
            $aStar = new Cli_Model_Astar($army, $position['x'], $position['y'], $fields);
        } catch (Exception $e) {
            echo($e);
            return;
        }
        $key = $position['x'] . '_' . $position['y'];
        $movesToSpend = $aStar->getMovesSpendForFullPath($key);
        if ($movesToSpend && $movesToSpend > ($army['movesLeft'] - 2)) {
            $in = false;
        } else {
            $in = true;
        }
        $path = $aStar->getPath($key, $army['movesLeft'] - 2);
        $currentPosition = $aStar->getCurrentPosition();
        if (!$currentPosition) {
            if ($in) {
                $currentPosition = array(
                    'x' => $position['x'],
                    'y' => $position['y'],
                    'movesSpend' => 2
                );
            } else {
                $currentPosition = array(
                    'x' => $army['x'],
                    'y' => $army['y'],
                    'movesSpend' => 0
                );
            }
        }
        return array(
            'path' => $path,
            'currentPosition' => $currentPosition,
            'in' => $in
        );
    }

    static public function isEnemyArmyInRange($castlesAndFields, $enemy, $army) {
        $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $castlesAndFields['hostileCastles']);
        if ($castleId !== null) {
            $castlesAndFields['fields'] = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'c');
        } else {
            $castlesAndFields['fields'] = Application_Model_Board::restoreField($castlesAndFields['fields'], $enemy['x'], $enemy['y']);
        }try {
            $aStar = new Cli_Model_Astar($army, $enemy['x'], $enemy['y'], $castlesAndFields['fields']);
        } catch (Exception $e) {
            echo($e);
            return;
        }

        $key = $enemy['x'] . '_' . $enemy['y'];
        $movesToSpend = $aStar->getMovesSpendForFullPath($key);
        if ($movesToSpend && $movesToSpend > ($army['movesLeft'] - 2)) {
            $in = false;
        } else {
            $in = true;
        }
        $path = $aStar->getPath($key, $army['movesLeft'] - 2);
        $currentPosition = $aStar->getCurrentPosition();
        if (!$currentPosition) {
            if ($in) {
                $currentPosition = array(
                    'x' => $enemy['x'],
                    'y' => $enemy['y'],
                    'movesSpend' => 2
                );
            } else {
                $currentPosition = array(
                    'x' => $army['x'],
                    'y' => $army['y'],
                    'movesSpend' => 0
                );
            }
        }
        return array(
            'path' => $path,
            'currentPosition' => $currentPosition,
            'in' => $in,
            'castleId' => $castleId
        );
    }

    static public function canEnemyReachThisCastle($castlePosition, $castlesAndFields, $enemies) {
        $enemiesHaveRange = array();
        foreach ($enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($castlePosition['x'], $castlePosition['y']);
            $h = $mHeuristics->calculateH($enemy['x'], $enemy['y']);
            if ($h < ($enemy['movesLeft'])) {
                $mArmy = new Cli_Model_Army($enemy);
                $enemy = $mArmy->getArmy();
                $castlesAndFields['fields'] = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $castlePosition['x'], $castlePosition['y'], 'c');
                try {
                    $aStar = new Cli_Model_Astar($enemy, $castlePosition['x'], $castlePosition['y'], $castlesAndFields['fields']);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $castlesAndFields['fields'] = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $castlePosition['x'], $castlePosition['y'], 'e');
                $movesToSpend = $aStar->getMovesSpendForFullPath($castlePosition['x'] . '_' . $castlePosition['y']);
                if ($movesToSpend && $movesToSpend <= ($enemy['movesLeft'] - 2)) {
                    $enemy['aStar'] = $aStar;
                    $enemy['key'] = $castlePosition['x'] . '_' . $castlePosition['y'];
                    $enemy['movesToSpend'] = $movesToSpend + 2;
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

    static public function getEnemiesInRange($enemies, $army, $fields) {
        $enemiesInRange = array();
        foreach ($enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($army['x'], $army['y']);
            $h = $mHeuristics->calculateH($enemy['x'], $enemy['y']);
            if ($h < $army['movesLeft']) {
                $destX = $enemy['x'];
                $destY = $enemy['y'];
                $fields = Application_Model_Board::restoreField($fields, $destX, $destY);
                try {
                    $aStar = new Cli_Model_Astar($army, $destX, $destY, $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }
                $movesToSpend = $aStar->getMovesSpendForFullPath($destX . '_' . $destY);
                $fields = Application_Model_Board::changeArmyField($fields, $destX, $destY, 'e');

                if ($movesToSpend && $movesToSpend <= ($army['movesLeft'] - 2)) {
                    $enemy['aStar'] = $aStar;
                    $enemy['key'] = $destX . '_' . $destY;
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

    static public function getNearestRuin($fields, $ruins, $army) {
        foreach ($ruins as $ruinId => $ruin) {
            $destX = $ruin['x'];
            $destY = $ruin['y'];
            $mHeuristics = new Cli_Model_Heuristics($destX, $destY);
            $h = $mHeuristics->calculateH($army['x'], $army['y']);
            if ($h < $army['movesLeft']) {
                try {
                    $aStar = new Cli_Model_Astar($army, $destX, $destY, $fields, true);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $key = $destX . '_' . $destY;
                $movesToSpend = $aStar->getMovesSpendForFullPath($key);
                if ($movesToSpend && $movesToSpend <= $army['movesLeft']) {
                    $ruin['path'] = $aStar->getPath($key, $army['movesLeft']);
                    $ruin['currentPosition'] = $aStar->getCurrentPosition();
                    $ruin['ruinId'] = $ruinId;
                    return $ruin;
                }
            }
        }
    }

    static public function getMyEmptyCastleInMyRange($gameId, $myCastles, $army, $fields, $db) {
        foreach ($myCastles as $castle) {
            $position = Application_Model_Board::getCastlePosition($castle['castleId']);
            if (Cli_Model_Database::areUnitsAtCastlePosition($gameId, $position, $db)) {
                continue;
            }
            $mHeuristics = new Cli_Model_Heuristics($army['x'], $army['y']);
            $h = $mHeuristics->calculateH($position['x'], $position['y']);
            if ($h < $army['movesLeft']) {
                $fields = Application_Model_Board::changeCasteFields($fields, $position['x'], $position['y'], 'c');
                try {
                    $aStar = new Cli_Model_Astar($army, $position['x'], $position['y'], $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $key = $position['x'] . '_' . $position['y'];
                $movesToSpend = $aStar->getMovesSpendForFullPath($key);
                $fields = Application_Model_Board::changeCasteFields($fields, $position['x'], $position['y'], 'e');
                if ($movesToSpend && $movesToSpend <= $army['movesLeft']) {
                    $castle['movesSpend'] = $movesToSpend;
                    $castle['path'] = $aStar->getPath($key, $army['movesLeft']);
                    $castle['currentPosition'] = $aStar->getCurrentPosition();
                    $castle['x'] = $position['x'];
                    $castle['y'] = $position['y'];
                    return $castle;
                }
            }
        }
    }

    static public function isMyCastleInRangeOfEnemy($enemies, $myEmptyCastle, $fields) {
        foreach ($enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy['x'], $enemy['y']);
            $h = $mHeuristics->calculateH($myEmptyCastle['x'], $myEmptyCastle['y']);
            if ($h < $enemy['movesLeft']) {
                $fields = Application_Model_Board::changeCasteFields($fields, $myEmptyCastle['x'], $myEmptyCastle['y'], 'c');
                $mArmy = new Cli_Model_Army($enemy);
                $enemy = $mArmy->getArmy();
                try {
                    $aStar = new Cli_Model_Astar($enemy, $myEmptyCastle['x'], $myEmptyCastle['y'], $fields);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $key = $myEmptyCastle['x'] . '_' . $myEmptyCastle['y'];
                $movesToSpend = $aStar->getMovesSpendForFullPath($key);
                if ($movesToSpend && $movesToSpend <= $enemy['movesLeft']) {
                    return true;
                }
                $fields = Application_Model_Board::changeCasteFields($fields, $myEmptyCastle['x'], $myEmptyCastle['y'], 'e');
            }
        }
    }

    static public function canAttackAllEnemyHaveRange($gameId, $playerId, $enemies, $army, $castles, $db = null) {
        foreach ($enemies as $enemy) {
            $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $castles);
            $enemy['castleId'] = $castleId;
            if (self::isEnemyStronger($gameId, $playerId, $db, $army, $enemy, $castleId)) {
                return null;
            }
        }
        return $enemy;
    }

    static public function getWeakerEnemyArmyInRange($gameId, $playerId, $enemies, $army, $castlesAndFields, $db) {
        foreach ($enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy['x'], $enemy['y']);
            $h = $mHeuristics->calculateH($army['x'], $army['y']);
            if ($h < $army['movesLeft']) {
                $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $castlesAndFields['hostileCastles']);
                if (self::isEnemyStronger($gameId, $playerId, $db, $army, $enemy, $castleId)) {
                    continue;
                }
                if ($castleId !== null) {
                    $castlesAndFields['fields'] = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'c');
                } else {
                    $castlesAndFields['fields'] = Application_Model_Board::restoreField($castlesAndFields['fields'], $enemy['x'], $enemy['y']);
                }try {
                    $aStar = new Cli_Model_Astar($army, $enemy['x'], $enemy['y'], $castlesAndFields['fields']);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $key = $enemy['x'] . '_' . $enemy['y'];
                $movesToSpend = $aStar->getMovesSpendForFullPath($key);
                if ($movesToSpend && $movesToSpend <= ($army['movesLeft'] - 2)) {
                    $enemy['movesSpend'] = $movesToSpend;
                    $enemy['path'] = $aStar->getPath($key, $movesToSpend);
                    $enemy['currentPosition'] = $aStar->getCurrentPosition();
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

    static public function getStrongerEnemyArmyInRange($gameId, $playerId, $enemies, $army, $castlesAndFields, $db) {
        foreach ($enemies as $enemy) {
            $mHeuristics = new Cli_Model_Heuristics($enemy['x'], $enemy['y']);
            $h = $mHeuristics->calculateH($army['x'], $army['y']);
            if ($h < $army['movesLeft']) {
                $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $castlesAndFields['hostileCastles']);
                if (!self::isEnemyStronger($gameId, $playerId, $db, $army, $enemy, $castleId)) {
                    continue;
                }
                if ($castleId !== null) {
                    $castlesAndFields['fields'] = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'c');
                } else {
                    $castlesAndFields['fields'] = Application_Model_Board::restoreField($castlesAndFields['fields'], $enemy['x'], $enemy['y']);
                }try {
                    $aStar = new Cli_Model_Astar($army, $enemy['x'], $enemy['y'], $castlesAndFields['fields']);
                } catch (Exception $e) {
                    echo($e);
                    return;
                }

                $key = $enemy['x'] . '_' . $enemy['y'];
                $movesToSpend = $aStar->getMovesSpendForFullPath($key);
                if ($movesToSpend && $movesToSpend <= ($army['movesLeft'] - 2)) {
                    $enemy['movesSpend'] = $movesToSpend;
                    $enemy['path'] = $aStar->getPath($key, $movesToSpend);
                    $enemy['currentPosition'] = $aStar->getCurrentPosition();
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

    static public function getMyArmyInRange($gameId, $playerId, $army, $fields, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
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

                $key = $a['x'] . '_' . $a['y'];
                $movesToSpend = $aStar->getMovesSpendForFullPath($key);
                if ($movesToSpend && $movesToSpend <= ($army['movesLeft'] - 2)) {
                    $a['movesSpend'] = $movesToSpend;
                    $a['path'] = $aStar->getPath($key, $movesToSpend);
                    $a['currentPosition'] = $aStar->getCurrentPosition();
                    return $a;
                }
            }
        }
        return null;
    }

    static public function getMyCastelNearEnemy($enemies, $army, $fields, $myCastles) {
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

        $castle['path'] = $aStar->getPath($position['x'] . '_' . $position['y'], $army['movesLeft']);
        $castle['currentPosition'] = $aStar->getCurrentPosition();
        if ($castle['currentPosition']) {
            return $castle;
        } else {
            return null;
        }
    }

    static public function getArmyCanFlySwim($army) {
        $canFly = -count($army['heroes']);
        $canSwim = 0;

        foreach ($army['soldiers'] as $soldier) {
            if ($soldier['canFly']) {
                $canFly++;
            } else {
                $canFly -= 200;
            }
            if ($soldier['canSwim']) {
                $canSwim++;
            }
        }
        return array('canFly' => $canFly, 'canSwim' => $canSwim);
    }

}

