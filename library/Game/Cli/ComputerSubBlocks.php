<?php

class Game_Cli_ComputerSubBlocks {

    static public function fightEnemy($gameId, $army, $enemy, $playerId, $castleId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $result = array(
            'victory' => false
        );

        if ($castleId !== null) { // castle
            if (Game_Cli_Database::isEnemyCastle($gameId, $castleId, $playerId, $db)) { // enemy castle
                $result['defenderColor'] = Game_Cli_Database::getColorByCastleId($gameId, $castleId, $db);
                $enemy = Game_Cli_Database::getAllUnitsFromCastlePosition($gameId, Application_Model_Board::getCastlePosition($castleId), $db);
                $battle = new Game_Cli_Battle($army, $enemy);
                $battle->addCastleDefenseModifier($gameId, $castleId, $db);
                $battle->fight();
                $battle->updateArmies($gameId, $db);
                $defender = Game_Cli_Database::updateAllArmiesFromCastlePosition($gameId, Application_Model_Board::getCastlePosition($castleId), $db);

                if (empty($defender)) {
                    $result['attackerArmy'] = Game_Cli_Database::getArmyByArmyIdPlayerId($gameId, $army['armyId'], $playerId, $db);
                    $result['victory'] = true;
                    foreach ($enemy['ids'] as $id)
                    {
                        $defender[]['armyId'] = $id;
                    }
                    var_dump('defender:');
                    print_r($defender);
                    Game_Cli_Database::changeOwner($gameId, $castleId, $playerId, $db);
                } else {
                    $result['attackerArmy'] = array(
                        'armyId' => $army['armyId'],
                        'destroyed' => true
                    );
                    Game_Cli_Database::destroyArmy($gameId, $army['armyId'], $playerId, $db);
                }
            } else { // neutral castle
                $enemy = Game_Cli_Battle::getNeutralCastleGarrizon($gameId, $db);
                $battle = new Game_Cli_Battle($army, $enemy);
                $battle->fight();
                $battle->updateArmies($gameId, $db);
                $defender = $battle->getDefender();

                if (empty($defender['soldiers'])) {
                    $result['attackerArmy'] = Game_Cli_Database::getArmyByArmyIdPlayerId($gameId, $army['armyId'], $playerId, $db);
                    Game_Cli_Database::addCastle($gameId, $castleId, $playerId, $db);
                    $result['victory'] = true;
                } else {
                    $result['attackerArmy'] = array(
                        'armyId' => $army['armyId'],
                        'destroyed' => true
                    );
                    Game_Cli_Database::destroyArmy($gameId, $army['armyId'], $playerId, $db);
                    $defender=null;
                }
                $result['defenderColor'] = 'neutral';
            }
        } else { // enemy army
            $battle = new Game_Cli_Battle($army, $enemy);
            $battle->addTowerDefenseModifier($enemy['x'], $enemy['y']);
            $battle->fight();
            $battle->updateArmies($gameId, $db);
            $defender = Game_Cli_Database::updateAllArmiesFromPosition($gameId, array('x' => $enemy['x'], 'y' => $enemy['y']), $db);

            if (empty($defender)) {
                $result['attackerArmy'] = Game_Cli_Database::getArmyByArmyIdPlayerId($gameId, $army['armyId'], $playerId, $db);
                $result['victory'] = true;
                $defender[0]['armyId'] = $enemy['armyId'];
            } else {
                $result['attackerArmy'] = array(
                    'armyId' => $army['armyId'],
                    'destroyed' => true
                );
                Game_Cli_Database::destroyArmy($gameId, $army['armyId'], $playerId, $db);
            }
            $result['defenderColor'] = Game_Cli_Database::getColorByArmyId($gameId, $enemy['armyId'], $db);
        }

        $result['defenderArmy'] = $defender;
        $result['battle'] = $battle->getResult($army, $enemy);

        return $result;
    }

    static public function isEnemyStronger($gameId, $playerId, $db, $army, $enemy, $castleId, $max = 30) {
        $attackerWinsCount = 0;
        $attackerCourage = 2;
        for ($i = 0; $i < $max; $i++)
        {
            $battle = new Game_Cli_Battle($army, $enemy);
            if ($castleId !== null) {
                if (Game_Cli_Database::isEnemyCastle($gameId, $castleId, $playerId, $db)) {
                    $battle->addCastleDefenseModifier($gameId, $castleId, $db);
                }
            }
            if (isset($enemy['x']) && isset($enemy['y'])) {
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
        foreach ($castles as $castleId => $castle)
        {
            $aStar = new Game_Cli_Astar($castle['position']['x'], $castle['position']['y']);
            $heuristics[$castleId] = $aStar->calculateH($army['x'], $army['y']);
        }
        asort($heuristics, SORT_NUMERIC);
//         $weaker = array();

        foreach (array_keys($heuristics) as $castleId)
        {
            if (Game_Cli_Database::isEnemyCastle($gameId, $castleId, $playerId, $db)) {
                $enemy = Game_Cli_Database::getAllUnitsFromCastlePosition($gameId, Application_Model_Board::getCastlePosition($castleId), $db);
            } else {
                $enemy = Game_Cli_Battle::getNeutralCastleGarrizon($gameId, $db);
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
        $aStar = new Game_Cli_Astar($position['x'], $position['y']);
        $aStar->start($army['x'], $army['y'], $fields, $army['canFly'], $army['canSwim']);
        $key = $position['x'] . '_' . $position['y'];
        $movesToSpend = $aStar->getFullPathMovesSpend($key);
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
        }
        $aStar = new Game_Cli_Astar($enemy['x'], $enemy['y']);
        $aStar->start($army['x'], $army['y'], $castlesAndFields['fields'], $army['canFly'], $army['canSwim']);
        $key = $enemy['x'] . '_' . $enemy['y'];
        $movesToSpend = $aStar->getFullPathMovesSpend($key);
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
        foreach ($enemies as $enemy)
        {
            $aStar = new Game_Cli_Astar($castlePosition['x'], $castlePosition['y']);
            $h = $aStar->calculateH($enemy['x'], $enemy['y']);
            if ($h < ($enemy['numberOfMoves'])) {
                $canFlySwim = self::getArmyCanFlySwim($enemy);
                $castlesAndFields['fields'] = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $castlePosition['x'], $castlePosition['y'], 'c');
                $aStar->start($enemy['x'], $enemy['y'], $castlesAndFields['fields'], $canFlySwim['canFly'], $canFlySwim['canSwim']);
                $castlesAndFields['fields'] = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $castlePosition['x'], $castlePosition['y'], 'e');
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
            return false;
        }
    }

    static public function getEnemiesInRange($enemies, $army, $fields) {
        $enemiesInRange = array();
        $srcX = $army['x'];
        $srcY = $army['y'];
        foreach ($enemies as $enemy)
        {
            $aStar = new Game_Cli_Astar($army['x'], $army['y']);
            $h = $aStar->calculateH($enemy['x'], $enemy['y']);
            if ($h < $army['movesLeft']) {
                $destX = $enemy['x'];
                $destY = $enemy['y'];
                $fields = Application_Model_Board::restoreField($fields, $destX, $destY);
                $aStar = new Game_Cli_Astar($destX, $destY);
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
        foreach ($ruins as $ruinId => $ruin)
        {
            $destX = $ruin['x'];
            $destY = $ruin['y'];
            $aStar = new Game_Cli_Astar($destX, $destY);
            $h = $aStar->calculateH($srcX, $srcY);
            if ($h < $army['movesLeft']) {
                $aStar->start($srcX, $srcY, $fields, $army['canFly'], $army['canSwim']);
                $key = $destX . '_' . $destY;
                $movesToSpend = $aStar->getFullPathMovesSpend($key);
                if ($movesToSpend && $movesToSpend <= $army['movesLeft']) {
                    $ruin['path'] = $aStar->getPath($key, $army['movesLeft']);
                    $ruin['currentPosition'] = $aStar->getCurrentPosition();
                    $ruin['ruinId'] = $ruinId;
                    return $ruin;
                }
            }
        }
    }

    static public function getMyEmptyCastleInMyRange($gameId, $myCastles, $army, $fields, $db = null) {
        foreach ($myCastles as $castle)
        {
            $position = Application_Model_Board::getCastlePosition($castle['castleId']);
            if (Game_Cli_Database::areUnitsAtCastlePosition($gameId, $position, $db)) {
                continue;
            }
            $aStar = new Game_Cli_Astar($army['x'], $army['y']);
            $h = $aStar->calculateH($position['x'], $position['y']);
            if ($h < $army['movesLeft']) {
                $fields = Application_Model_Board::changeCasteFields($fields, $position['x'], $position['y'], 'c');
                $aStar = new Game_Cli_Astar($position['x'], $position['y']);
                $aStar->start($army['x'], $army['y'], $fields, $army['canFly'], $army['canSwim']);
                $key = $position['x'] . '_' . $position['y'];
                $movesToSpend = $aStar->getFullPathMovesSpend($key);
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
        foreach ($enemies as $enemy)
        {
            $aStar = new Game_Cli_Astar($enemy['x'], $enemy['y']);
            $h = $aStar->calculateH($myEmptyCastle['x'], $myEmptyCastle['y']);
            if ($h < $enemy['numberOfMoves']) {
                $fields = Application_Model_Board::changeCasteFields($fields, $myEmptyCastle['x'], $myEmptyCastle['y'], 'c');
                $aStar = new Game_Cli_Astar($myEmptyCastle['x'], $myEmptyCastle['y']);
                $canFlySwim = self::getArmyCanFlySwim($enemy);
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

    static public function canAttackAllEnemyHaveRange($gameId, $playerId, $enemies, $army, $castles, $db = null) {
        foreach ($enemies as $enemy)
        {
            $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $castles);
            $enemy['castleId'] = $castleId;
            if (self::isEnemyStronger($gameId, $playerId, $db, $army, $enemy, $castleId)) {
                return null;
            }
        }
        return $enemy;
    }

    static public function getWeakerEnemyArmyInRange($gameId, $playerId, $enemies, $army, $castlesAndFields, $db = null) {
        foreach ($enemies as $enemy)
        {
            $aStar = new Game_Cli_Astar($enemy['x'], $enemy['y']);
            $h = $aStar->calculateH($army['x'], $army['y']);
            if ($h < $army['movesLeft']) {
                $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $castlesAndFields['hostileCastles']);
                if (self::isEnemyStronger($gameId, $playerId, $db, $army, $enemy, $castleId)) {
                    continue;
                }
                if ($castleId !== null) {
                    $castlesAndFields['fields'] = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'c');
                } else {
                    $castlesAndFields['fields'] = Application_Model_Board::restoreField($castlesAndFields['fields'], $enemy['x'], $enemy['y']);
                }
                $aStar->start($army['x'], $army['y'], $castlesAndFields['fields'], $army['canFly'], $army['canSwim']);
                $key = $enemy['x'] . '_' . $enemy['y'];
                $movesToSpend = $aStar->getFullPathMovesSpend($key);
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
        foreach ($enemies as $enemy)
        {
            $aStar = new Game_Cli_Astar($enemy['x'], $enemy['y']);
            $h = $aStar->calculateH($army['x'], $army['y']);
            if ($h < $army['movesLeft']) {
                $castleId = Application_Model_Board::isCastleAtPosition($enemy['x'], $enemy['y'], $castlesAndFields['hostileCastles']);
                if (!self::isEnemyStronger($gameId, $playerId, $db, $army, $enemy, $castleId)) {
                    continue;
                }
                if ($castleId !== null) {
                    $castlesAndFields['fields'] = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $enemy['x'], $enemy['y'], 'c');
                } else {
                    $castlesAndFields['fields'] = Application_Model_Board::restoreField($castlesAndFields['fields'], $enemy['x'], $enemy['y']);
                }
                $aStar->start($army['x'], $army['y'], $castlesAndFields['fields'], $army['canFly'], $army['canSwim']);
                $key = $enemy['x'] . '_' . $enemy['y'];
                $movesToSpend = $aStar->getFullPathMovesSpend($key);
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

    static public function getMyArmyInRange($gameId, $army, $fields, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $myArmies = Game_Cli_Database::getAllPlayerArmiesExeptOne($gameId, $army['armyId'], $army['playerId'], $db);
        foreach ($myArmies as $a)
        {
            $aStar = new Game_Cli_Astar($a['x'], $a['y']);
            $h = $aStar->calculateH($army['x'], $army['y']);
            if ($h < $army['movesLeft']) {
                $aStar->start($army['x'], $army['y'], $fields, $army['canFly'], $army['canSwim']);
                $key = $a['x'] . '_' . $a['y'];
                $movesToSpend = $aStar->getFullPathMovesSpend($key);
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
        foreach ($enemies as $k => $enemy)
        {
            $aStar = new Game_Cli_Astar($enemy['x'], $enemy['y']);
            $heuristics[$k] = $aStar->calculateH($army['x'], $army['y']);
        }
        if (empty($heuristics)) {
            return null;
        }
        asort($heuristics, SORT_NUMERIC);
        $k = key($heuristics);
        $heuristics = array();
        foreach ($myCastles as $j => $castle)
        {
            $position = Application_Model_Board::getCastlePosition($castle['castleId']);
            $aStar = new Game_Cli_Astar($enemies[$k]['x'], $enemies[$k]['y']);
            $heuristics[$j] = $aStar->calculateH($position['x'], $position['y']);
        }
        if (empty($heuristics)) {
            return null;
        }
        asort($heuristics, SORT_NUMERIC);
        $k = key($heuristics);
        $castle = $myCastles[$k];
        $position = Application_Model_Board::getCastlePosition($castle['castleId']);
        $aStar = new Game_Cli_Astar($position['x'], $position['y']);
        $aStar->start($army['x'], $army['y'], $fields, $army['canFly'], $army['canSwim']);
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

        foreach ($army['soldiers'] as $soldier)
        {
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
