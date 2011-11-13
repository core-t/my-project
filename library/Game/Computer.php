<?php

class Game_Computer {

    static public function fightEnemy($army, $enemy, $playerId, $castleId) {
        $namespace = Game_Namespace::getNamespace();
        $modelArmy = new Application_Model_Army($namespace->gameId);
        $modelCastle = new Application_Model_Castle($namespace->gameId);
        $result = array();
        if ($castleId !== null) {
            if ($modelCastle->isEnemyCastle($castleId, $playerId)) {
                $enemy = $modelArmy->getAllUnitsFromCastlePosition(Application_Model_Board::getCastlePosition($castleId));
                $battle = new Game_Battle($army, $enemy);
                $battle->addCastleDefenseModifier(Application_Model_Board::getCastleDefense($castleId) + $modelCastle->getCastleDefenseModifier($castleId));
                $battle->fight();
                $battle->updateArmies();
                $enemy = $modelArmy->updateAllArmiesFromCastlePosition(Application_Model_Board::getCastlePosition($castleId));
                if (empty($enemy)) {
                    $modelCastle->changeOwner($castleId, $playerId);
                    $result['victory'] = true;
                } else {
                    $modelArmy->destroyArmy($army['armyId'], $playerId);
                    $result['victory'] = false;
                }
            } else {
                $battle = new Game_Battle($army, null);
                $enemy = $battle->getNeutralCastleGarrizon();
                $battle->fight();
                $battle->updateArmies();
                $defender = $battle->getDefender();
                if (empty($defender['soldiers'])) {
                    $modelCastle->addCastle($castleId, $playerId);
                    $result['victory'] = true;
                } else {
                    $modelArmy->destroyArmy($army['armyId'], $playerId);
                    $result['victory'] = false;
                }
            }
        } else {
            $battle = new Game_Battle($army, $enemy);
            $battle->addTowerDefenseModifier($enemy['x'], $enemy['y']);
            $battle->fight();
            $battle->updateArmies();
            $enemy = $modelArmy->updateAllArmiesFromPosition(array('x' => $enemy['x'], 'y' => $enemy['y']));
            if (empty($enemy)) {
                $result['victory'] = true;
            } else {
                $modelArmy->destroyArmy($army['armyId'], $playerId);
                $result['victory'] = false;
            }
        }
        $result['battle'] = $battle->getResult();
        return $result;
    }

    static public function isEnemyStronger($army, $enemy, $castleId=null, $max = 20) {
        $namespace = Game_Namespace::getNamespace();
        $modelArmy = new Application_Model_Army($namespace->gameId);
        $modelCastle = new Application_Model_Castle($namespace->gameId);
        $attackerCount = 0;
        for ($i = 0; $i < $max; $i++) {
            $battle = new Game_Battle($army, $enemy);
            if ($castleId !== null) {
                $battle->addCastleDefenseModifier(Application_Model_Board::getCastleDefense($castleId) + $modelCastle->getCastleDefenseModifier($castleId));
            }
            if (isset($enemy['x']) && isset($enemy['y'])) {
                $battle->addTowerDefenseModifier($enemy['x'], $enemy['y']);
            }
            $battle->fight();
            if ($battle->getAttacker()) {
                $attackerCount++;
            }
        }
        $border = $max - $attackerCount;
        new Game_Logger('attackerCount ' . $attackerCount . ' >= ' . $border);
        if ($attackerCount >= $border) {
            new Game_Logger('ENEMY SŁABSZY');
            return false;
        } else {
            new Game_Logger('ENEMY SILNIEJSZY');
            return true;
        }
    }

    static public function getWeakerEnemyCastle($castles, $army, $playerId) {
        $namespace = Game_Namespace::getNamespace();
        $modelArmy = new Application_Model_Army($namespace->gameId);
        $modelCastle = new Application_Model_Castle($namespace->gameId);
        $heuristics = array();
        foreach ($castles as $castleId => $castle) {
            $aStar = new Game_Astar($castle['position']['x'], $castle['position']['y']);
            $heuristics[$castleId] = $aStar->calculateH($army['x'], $army['y']);
        }
        asort($heuristics, SORT_NUMERIC);
//         $weaker = array();
        foreach ($heuristics as $castleId => $heuristic) {
            if ($modelCastle->isEnemyCastle($castleId, $playerId)) {
                $enemy = $modelArmy->getAllUnitsFromCastlePosition(Application_Model_Board::getCastlePosition($castleId));
            } else {
                $enemy = Game_Battle::getNeutralCastleGarrizon();
            }
            if (!self::isEnemyStronger($army, $enemy, $castleId)) {
                new Game_Logger('ENEMY SŁABSZY - 108');
                return $castleId;
            }
//             $weaker[$castleId] = Game_Battle::getCastlePower($castleId, $playerId);
        }
//         asort($weaker, SORT_NUMERIC);
        return null;
    }

    static public function isCastleInRange($castlesAndFields, $castleId, $army) {
        $position = Application_Model_Board::getCastlePosition($castleId);
        $fields = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $position['x'], $position['y'], 'c');
        $aStar = new Game_Astar($position['x'], $position['y']);
        $aStar->start($army['x'], $army['y'], $fields, $army['canFly'], $army['canSwim']);
        $key = $position['x'] . '_' . $position['y'];
        $movesToSpend = $aStar->getFullPathMovesSpend($key);
        if ($movesToSpend && $movesToSpend > ($army['movesLeft'] - 2)) {
            $in = false;
        } else {
            $in = true;
        }
        $path = $aStar->restorePath($key, $army['movesLeft'] - 2);
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

    static public function isEnemyInRange($castlesAndFields, $enemy, $army) {
        $fields = Application_Model_Board::restoreField($castlesAndFields['fields'], $enemy['x'], $enemy['y']);
        $aStar = new Game_Astar($enemy['x'], $enemy['y']);
        $aStar->start($army['x'], $army['y'], $fields, $army['canFly'], $army['canSwim']);
        $key = $enemy['x'] . '_' . $enemy['y'];
        $movesToSpend = $aStar->getFullPathMovesSpend($key);
        if ($movesToSpend && $movesToSpend > ($army['movesLeft'] - 2)) {
            $in = false;
        } else {
            $in = true;
        }
        $path = $aStar->restorePath($key, $army['movesLeft'] - 2);
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
            'in' => $in
        );
    }

    static public function canEnemyReachThisCastle($castlePosition, $castlesAndFields, $enemies) {
        $namespace = Game_Namespace::getNamespace();
        $modelArmy = new Application_Model_Army($namespace->gameId);
        $heuristics = array();
        $enemiesHaveRange = array();
        foreach ($enemies as $enemy) {
            $aStar = new Game_Astar($castlePosition['x'], $castlePosition['y']);
            $h = $aStar->calculateH($enemy['x'], $enemy['y']);
            if ($h < ($enemy['numberOfMoves'])) {
                $canFlySwim = $modelArmy->getArmyCanFlySwim($enemy);
                $castlesAndFields['fields'] = Application_Model_Board::restoreField($castlesAndFields['fields'], $castlePosition['x'], $castlePosition['y']);
                $aStar->start($enemy['x'], $enemy['y'], $castlesAndFields['fields'], $canFlySwim['canFly'], $canFlySwim['canSwim']);
                $castlesAndFields['fields'] = Application_Model_Board::changeArmyField($castlesAndFields['fields'], $castlePosition['x'], $castlePosition['y'], 'e');
                $movesToSpend = $aStar->getFullPathMovesSpend($castlePosition['x'] . '_' . $castlePosition['y']);
                if ($movesToSpend && $movesToSpend <= ($enemy['numberOfMoves'] - 2)) {
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
//             new Game_Logger('BRAK WROGA, KTÓRY MA ZASIĘG NA ZAMEK');
            return false;
        }
    }

    static public function getEnemiesInRange($enemies, $army, $fields) {
        $heuristics = array();
        $enemiesInRange = array();
        $srcX = $army['x'];
        $srcY = $army['y'];
        foreach ($enemies as $enemy) {
            $aStar = new Game_Astar($army['x'], $army['y']);
            $h = $aStar->calculateH($enemy['x'], $enemy['y']);
            if ($h < $army['movesLeft']) {
                $destX = $enemy['x'];
                $destY = $enemy['y'];
                $fields = Application_Model_Board::restoreField($fields, $destX, $destY);
                $aStar = new Game_Astar($destX, $destY);
                $aStar->start($srcX, $srcY, $fields, $army['canFly'], $army['canSwim']);
                $movesToSpend = $aStar->getFullPathMovesSpend($destX . '_' . $destY);
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
        $srcX = $army['x'];
        $srcY = $army['y'];
        foreach ($ruins as $ruinId => $ruin) {
            $destX = $ruin['x'];
            $destY = $ruin['y'];
            $aStar = new Game_Astar($destX, $destY);
            $h = $aStar->calculateH($srcX, $srcY);
            if ($h < $army['movesLeft']) {
                $aStar->start($srcX, $srcY, $fields, $army['canFly'], $army['canSwim']);
                $key = $destX . '_' . $destY;
                $movesToSpend = $aStar->getFullPathMovesSpend($key);
                if ($movesToSpend && $movesToSpend <= $army['movesLeft']) {
                    $ruin['path'] = $aStar->restorePath($key, $army['movesLeft']);
                    $ruin['currentPosition'] = $aStar->getCurrentPosition();
                    $ruin['ruinId'] = $ruinId;
                    return $ruin;
                }
            }
        }
    }

    static public function getMyEmptyCastleInMyRange($myCastles, $army, $fields) {
        $namespace = Game_Namespace::getNamespace();
        $modelArmy = new Application_Model_Army($namespace->gameId);
        foreach ($myCastles as $castle) {
            $position = Application_Model_Board::getCastlePosition($castle['castleId']);
            if ($modelArmy->areUnitsAtCastlePosition($position)) {
                continue;
            }
            $aStar = new Game_Astar($army['x'], $army['y']);
            $h = $aStar->calculateH($position['x'], $position['y']);
            if ($h < $army['movesLeft']) {
                $fields = Application_Model_Board::changeCasteFields($fields, $position['x'], $position['y'], 'c');
                $aStar = new Game_Astar($position['x'], $position['y']);
                $aStar->start($army['x'], $army['y'], $fields, $army['canFly'], $army['canSwim']);
                $key = $position['x'] . '_' . $position['y'];
                $movesToSpend = $aStar->getFullPathMovesSpend($key);
                $fields = Application_Model_Board::changeCasteFields($fields, $position['x'], $position['y'], 'e');
                if ($movesToSpend && $movesToSpend <= $army['movesLeft']) {
                    $castle['movesSpend'] = $movesToSpend;
                    $castle['path'] = $aStar->restorePath($key, $army['movesLeft']);
                    $castle['currentPosition'] = $aStar->getCurrentPosition();
                    $castle['x'] = $position['x'];
                    $castle['y'] = $position['y'];
                    return $castle;
                }
            }
        }
    }

    static public function isMyCastleInRangeOfEnemy($enemies, $myEmptyCastle, $fields) {
        $namespace = Game_Namespace::getNamespace();
        $modelArmy = new Application_Model_Army($namespace->gameId);
        foreach ($enemies as $enemy) {
            $aStar = new Game_Astar($enemy['x'], $enemy['y']);
            $h = $aStar->calculateH($myEmptyCastle['x'], $myEmptyCastle['y']);
            if ($h < $enemy['numberOfMoves']) {
                $fields = Application_Model_Board::changeCasteFields($fields, $myEmptyCastle['x'], $myEmptyCastle['y'], 'c');
                $aStar = new Game_Astar($myEmptyCastle['x'], $myEmptyCastle['y']);
                $canFlySwim = $modelArmy->getArmyCanFlySwim($enemy);
                $aStar->start($enemy['x'], $enemy['y'], $fields, $canFlySwim['canFly'], $canFlySwim['canSwim']);
                $key = $myEmptyCastle['x'] . '_' . $myEmptyCastle['y'];
                $movesToSpend = $aStar->getFullPathMovesSpend($key);
                if ($movesToSpend && $movesToSpend <= $enemy['numberOfMoves']) {
                    return true;
                }
                $fields = Application_Model_Board::changeCasteFields($fields, $myEmptyCastle['x'], $myEmptyCastle['y'], 'e');
            }
        }
    }

    static public function canAttackAllEnemyHaveRange($enemies, $army, $castles) {
        foreach ($enemies as $enemy) {
            $castleId = Application_Model_Board::isArmyInCastle($enemy['x'], $enemy['y'], $castles);
            $enemy['castleId'] = $castleId;
            if (self::isEnemyStronger($army, $enemy, $castleId)) {
                new Game_Logger('ENEMY SILNIEJSZY - 322');
                return null;
            }
        }
        return $enemy;
    }

}

