<?php

class Game_Computer {

    public function __construct() {

    }

    static public function fightEnemy($army, $enemy, $playerId, $castleId) {
        $namespace = Game_Namespace::getNamespace();
        $modelArmy = new Application_Model_Army($namespace->gameId);
        $modelCastle = new Application_Model_Castle($namespace->gameId);
        $result = array();
//         $result['path'] = $range['path'];
//         $result['currentPosition'] = $range['currentPosition'];
        if ($castleId) {
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
//                     $result = Game_Astar::rewindPathOutOfCastle($result['path'], $result['currentPosition'], Application_Model_Board::getCastlePosition($castleId));
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
//                     $result = Game_Astar::rewindPathOutOfCastle($result['path'], $result['currentPosition'], Application_Model_Board::getCastlePosition($castleId));
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
//                 $result = Game_Astar::rewindPathOutOfArmy($result['path'], $result['currentPosition'], $enemy['x'], $enemy['y']);
                $modelArmy->destroyArmy($army['armyId'], $playerId);
                $result['victory'] = false;
            }
        }
        $result['battle'] = $battle->getResult();
        return $result;
    }

    static public function isEnemyStronger($army, $enemy, $castleId=null) {
        $namespace = Game_Namespace::getNamespace();
        $modelArmy = new Application_Model_Army($namespace->gameId);
        $modelCastle = new Application_Model_Castle($namespace->gameId);
        $attackerCount = 0;
        $battle = new Game_Battle($army, $enemy);
        if ($castleId) {
            $battle->addCastleDefenseModifier(Application_Model_Board::getCastleDefense($castleId) + $modelCastle->getCastleDefenseModifier($castleId));
        }
        if (isset($enemy['x']) && isset($enemy['y'])) {
            $battle->addTowerDefenseModifier($enemy['x'], $enemy['y']);
        }
        $battle->fight();
        if ($battle->getAttacker()) {
            $attackerCount++;
        }
        $battle = new Game_Battle($army, $enemy);
        if ($castleId) {
            $battle->addCastleDefenseModifier(Application_Model_Board::getCastleDefense($castleId) + $modelCastle->getCastleDefenseModifier($castleId));
        }
        if (isset($enemy['x']) && isset($enemy['y'])) {
            $battle->addTowerDefenseModifier($enemy['x'], $enemy['y']);
        }
        $battle->fight();
        if ($battle->getAttacker()) {
            $attackerCount++;
        }
        new Game_Logger('attackerCount=>' . $attackerCount);
        if ($attackerCount) {
            return false;
        }
        return true;
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
            if($modelCastle->isEnemyCastle($castleId, $playerId)){
                $enemy = $modelArmy->getAllUnitsFromCastlePosition(Application_Model_Board::getCastlePosition($castleId));
            }else{
                $enemy = Game_Battle::getNeutralCastleGarrizon();
            }
            if(!self::isEnemyStronger($army, $enemy, $castleId)){
                return $castleId;
            }
//             $weaker[$castleId] = Game_Battle::getCastlePower($castleId, $playerId);
        }
//         asort($weaker, SORT_NUMERIC);
    }

    static public function isCastleInRange($castlesAndFields, $castleId, $army) {
        $position = Application_Model_Board::getCastlePosition($castleId);
        $fields = Application_Model_Board::changeCasteFields($castlesAndFields['fields'], $position['x'], $position['y'], 'c');
        $aStar = new Game_Astar($position['x'], $position['y']);
        $aStar->start($army['x'], $army['y'], $fields, $army['canFly'], $army['canSwim']);
        $key = $position['x'] . '_' . $position['y'];
        $movesToSpend = $aStar->getFullPathMovesSpend($key);
        if ($movesToSpend > ($army['movesLeft'] - 2)) {
            $in = false;
        } else {
            $in = true;
        }
        $path = $aStar->restorePath($key, $army['movesLeft'] - 2);
        $currentPosition = $aStar->getCurrentPosition();
        if(!$currentPosition){
            if($in){
                $currentPosition = array(
                    'x' => $position['x'],
                    'x' => $position['y'],
                    'movesSpend' => 2
                );
            }else{
                $currentPosition = array(
                    'x' => $army['x'],
                    'x' => $army['y'],
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
        if ($movesToSpend > ($army['movesLeft'] - 2)) {
            $in = false;
        } else {
            $in = true;
        }
        $path = $aStar->restorePath($key, $army['movesLeft'] - 2);
        $currentPosition = $aStar->getCurrentPosition();
        if(!$currentPosition){
            if($in){
                $currentPosition = array(
                    'x' => $enemy['x'],
                    'x' => $enemy['y'],
                    'movesSpend' => 2
                );
            }else{
                $currentPosition = array(
                    'x' => $army['x'],
                    'x' => $army['y'],
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

    public function canEnemyReachThisCastle($castlePosition, $castlesAndFields, $enemies) {
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
                if ($movesToSpend <= ($enemy['numberOfMoves'] - 2)) {
                    $enemy['aStar'] = $aStar;
                    $enemy['key'] = $castlePosition['x'] . '_' . $castlePosition['y'];
                    $enemiesHaveRange[] = $enemy;
                }
            }
        }
        if (!empty($enemiesHaveRange)) {
            return $enemiesHaveRange;
        } else {
            new Game_Logger('BRAK WROGA, KTÓRY MA ZASIĘG NA ZAMEK');
            return false;
        }
    }

    public function getEnemiesInRange($enemies, $army, $fields) {
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
                if ($movesToSpend <= ($army['movesLeft'] - 2)) {
                    $enemy['aStar'] = $aStar;
                    $enemy['key'] = $destX . '_' . $destY;
                    $enemiesInRange[] = $enemy;
                }
            }
        }
        if (!empty($enemiesInRange)) {
            return $enemiesInRange;
        } else {
            new Game_Logger('BRAK WROGA W ZASIĘGU ARMII');
            return false;
        }
    }

    public function getNearestRuin($fields, $ruins, $army) {
        foreach ($ruins as $ruin) {
            $aStar = new Game_Astar($army['x'], $army['y']);
            $h = $aStar->calculateH($ruin['x'], $ruin['y']);
            if ($h < $army['movesLeft']) {
                $fields = Application_Model_Board::restoreField($fields, $ruin['x'], $ruin['y']);
                $aStar = new Game_Astar($ruin['x'], $ruin['y']);
                $aStar->start($army['x'], $army['y'], $fields, $army['canFly'], $army['canSwim']);
                $key = $ruin['x'] . '_' . $ruin['y'];
                $movesToSpend = $aStar->getFullPathMovesSpend($key);
                $fields = Application_Model_Board::changeArmyField($fields, $ruin['x'], $ruin['y'], 'e');
                if ($movesToSpend <= $army['movesLeft']) {
                    $ruin['movesSpend'] = $movesToSpend;
                    $ruin['path'] = $aStar->restorePath($key, $army['movesLeft']);
                    $ruin['currentPosition'] = $aStar->getCurrentPosition();
                    return $ruin;
                }
            }
        }
    }

    public function getMyEmptyCastleInMyRange($myCastles, $army, $fields){
        $namespace = Game_Namespace::getNamespace();
        $modelArmy = new Application_Model_Army($namespace->gameId);
        foreach($myCastles as $castle){
            $position = Application_Model_Board::getCastlePosition($castle['castleId']);
            if($modelArmy->getAllUnitsFromCastlePosition($position)){
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
                if ($movesToSpend <= $army['movesLeft']) {
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

    static public function isMyCastleInRangeOfEnemy($enemies, $myEmptyCastle){
        foreach($enemies as $enemy){
            $aStar = new Game_Astar($enemy['x'], $enemy['y']);
            $h = $aStar->calculateH($myEmptyCastle['x'], $myEmptyCastle['y']);
            if ($h < $enemy['movesLeft']) {
                $fields = Application_Model_Board::changeCasteFields($fields, $myEmptyCastle['x'], $myEmptyCastle['y'], 'c');
                $aStar = new Game_Astar($myEmptyCastle['x'], $myEmptyCastle['y']);
                $aStar->start($enemy['x'], $enemy['y'], $fields, $enemy['canFly'], $enemy['canSwim']);
                $key = $myEmptyCastle['x'] . '_' . $myEmptyCastle['y'];
                $movesToSpend = $aStar->getFullPathMovesSpend($key);
                if ($movesToSpend <= $enemy['movesLeft']) {
                    return true;
                }
                $fields = Application_Model_Board::changeCasteFields($fields, $myEmptyCastle['x'], $myEmptyCastle['y'], 'e');
            }
        }
    }

    static public function canAttackAllEnemyHaveRange($enemies, $army, $castlesAndFields){

    }

}

