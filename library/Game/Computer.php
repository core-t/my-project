<?php

class Game_Computer {

    private $playerId;
    private $army;
    private $path;
    private $currentPosition;
    private $castleId;
    private $victory = false;
    private $battle = null;
    private $inCastle = false;
    private $movesSpend = null;

    public function __construct($playerId, $army, $modelArmy) {
        $this->playerId = $playerId;
        $this->army = $army;
        $this->modelArmy = $modelArmy;
    }

    public function getPath() {
        return $this->path;
    }

    public function getCurrentPosition() {
        return $this->currentPosition;
    }

    public function getCastleId() {
        return $this->castleId;
    }

    public function getVictory() {
        return $this->victory;
    }

    public function getInCastle() {
        return $this->inCastle;
    }

    public function getBattle() {
        return $this->battle;
    }

    public function getMovesSpend() {
        return $this->movesSpend;
    }

    public function fightCastle($modelCastle, $castleId) {
        $victory = false;
        if (($this->army['movesLeft'] - $this->movesSpend) < 2) {
            $result = Game_Astar::rewindPathOutOfCastle($this->path, $this->currentPosition, Application_Model_Board::getCastlePosition($castleId));
            $this->path = $result['path'];
            $this->currentPosition = $result['currentPosition'];
            $this->modelArmy->zeroArmyMovesLeft($this->army['armyId'], $this->playerId);
        } else {
            $this->movesSpend += 2;
            if ($modelCastle->isEnemyCastle($castleId, $this->playerId)) {
                $enemy = $this->modelArmy->getAllUnitsFromCastlePosition(Application_Model_Board::getCastlePosition($castleId));
                if ($this->isEnemyStronger($this->army, $enemy, $castleId, $modelCastle)) {
                    $this->modelArmy->zeroArmyMovesLeft($this->army['armyId'], $this->playerId);
                } else {
                    $battle = new Game_Battle($this->army, $enemy);
                    $battle->addCastleDefenseModifier(Application_Model_Board::getCastleDefense($castleId) + $modelCastle->getCastleDefenseModifier($castleId));
                    $battle->fight();
                    $battle->updateArmies();
                    $this->battle = $battle->getResult();
                    $enemy = $this->modelArmy->updateAllArmiesFromCastlePosition(Application_Model_Board::getCastlePosition($castleId));
                    if (empty($enemy)) {
                        $modelCastle->changeOwner($castleId, $this->playerId);
                        $victory = true;
                    } else {
                        $result = Game_Astar::rewindPathOutOfCastle($this->path, $this->currentPosition, Application_Model_Board::getCastlePosition($castleId));
                        $this->path = $result['path'];
                        $this->currentPosition = $result['currentPosition'];
                        $this->modelArmy->destroyArmy($this->army['armyId'], $this->playerId);
                        $victory = false;
                    }
                }
            } else {
                $battle = new Game_Battle($this->army, null);
                $enemy = $battle->getNeutralCastleGarrizon();
                if ($this->isEnemyStronger($this->army, $enemy, $castleId, $modelCastle)) {
                    $this->modelArmy->zeroArmyMovesLeft($this->army['armyId'], $this->playerId);
                } else {
                    $battle->fight();
                    $battle->updateArmies();
                    $this->battle = $battle->getResult();
                    $defender = $battle->getDefender();
                    if (empty($defender['soldiers'])) {
                        $modelCastle->addCastle($castleId, $this->playerId);
                        $victory = true;
                    } else {
                        $result = Game_Astar::rewindPathOutOfCastle($this->path, $this->currentPosition, Application_Model_Board::getCastlePosition($castleId));
                        $this->path = $result['path'];
                        $this->currentPosition = $result['currentPosition'];
                        $this->modelArmy->destroyArmy($this->army['armyId'], $this->playerId);
                        $victory = false;
                    }
                }
            }
        }
        return $victory;
    }

    public function handleEnemyIsNearCastle($castlePosition, $modelArmy, $castlesAndFields, $modelCastle) {
        $castles = $castlesAndFields['hostileCastles'];
        $fields = $castlesAndFields['fields'];
        $armyIds = $modelArmy->getAllArmiesIdsFromCastlePosition($castlePosition);
        if ($armyIds) {
            $enemies = $modelArmy->getAllEnemiesArmies($this->playerId);
            $heuristics = array();
            foreach ($enemies as $id => $enemy) {
                $position = $modelArmy->convertPosition($enemy['position']);
                $aStar = new Game_Astar($castlePosition['x'], $castlePosition['y']);
                $h = $aStar->calculateH($position[0], $position[1]);
                if ($h < ($enemy['numberOfMoves'] * 40)) {

                    $heuristics[$id] = $h;
                }
            }
            if (!empty($heuristics)) {
                new Game_Logger($heuristics);
                $enemiesInRange = array();
                $enemiesOutOfRange = array();
                foreach ($armyIds as $armyId) {
                    $army = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->playerId);
                    $canFlySwim = $modelArmy->getArmyCanFlySwim($army);
                    $armyPosition = $modelArmy->convertPosition($army['position']);
                    $srcX = $armyPosition[0] / 40;
                    $srcY = $armyPosition[1] / 40;
                    foreach ($heuristics as $enemyArmyId => $v) {
                        $enemy = $enemies[$enemyArmyId];
                        if ($v <= ($army['movesLeft'] * 40)) {
                            $position = $modelArmy->convertPosition($enemy['position']);
                            $destX = $position[0] / 40;
                            $destY = $position[1] / 40;
                            $fields = Application_Model_Board::changeCasteFields($fields, $destX, $destY, 'c');
                            $aStar = new Game_Astar($destX, $destY);
                            $aStar->start($srcX, $srcY, $fields, $canFlySwim['canFly'], $canFlySwim['canSwim']);
                            $movesToSpend = $aStar->getFullPathMovesSpend($destX . '_' . $destY);
                            $fields = Application_Model_Board::changeCasteFields($fields, $destX, $destY, 'e');
                            if ($enemy['numberOfMoves'] >= ($movesToSpend + 2)) {
                                if ($movesToSpend <= ($army['movesLeft'] + 2)) {
                                    $enemy['aStar'] = $aStar;
                                    $enemy['key'] = $destX . '_' . $destY;
                                    $enemiesInRange[] = $enemy;
                                } else {
                                    $enemiesOutOfRange[] = $enemy;
                                }
                            }
                        }
                    }
//                     new Game_Logger('In range');
//                     new Game_Logger($enemiesInRange);
//                     new Game_Logger('Out of range');
//                     new Game_Logger($enemiesOutOfRange);
                    if (count($enemiesOutOfRange)) {
                        $modelArmy->zeroArmyMovesLeft($armyId, $this->playerId);
                    } else {
                        foreach ($enemiesInRange as $enemy) {
                            new Game_Logger($enemy);
                            $path = $enemy['aStar']->restorePath($enemy['key'], $army['movesLeft']);
                            $currentPosition = $enemy['aStar']->getCurrentPosition();
                            if ($armyPosition[0] == $currentPosition['x'] && $armyPosition[1] == $currentPosition['y']) {
                                new Game_Logger('Dojdę');
                                $castleId = Application_Model_Board::isArmyInCastle($position, $castles);
                                if ($castleId) {
                                    $enemy = $modelArmy->getAllUnitsFromCastlePosition(Application_Model_Board::getCastlePosition($castleId));
                                }
                                if ($this->isEnemyStronger($army, $enemy, $castleId, $modelCastle)) {
                                    new Game_Logger('Stronger');
                                    $modelArmy->zeroArmyMovesLeft($armyId, $this->playerId);
                                } else {
                                    new Game_Logger('Battle');
                                    $battle = new Game_Battle($army, $enemy);
                                    $battle->addCastleDefenseModifier($castleId);
                                    $battle->fight();
                                    $battle->updateArmies();
                                    $this->battle = $battle->getResult();
                                    $enemy = $modelArmy->updateAllArmiesFromCastlePosition(Application_Model_Board::getCastlePosition($castleId));
                                    if (empty($enemy)) {
                                        $modelCastle->changeOwner($castleId, $this->playerId);
                                        $victory = true;
                                    } else {
                                        $result = Game_Astar::rewindPathOutOfCastle($this->path, $this->currentPosition, Application_Model_Board::getCastlePosition($castleId));
                                        $this->path = $result['path'];
                                        $this->currentPosition = $result['currentPosition'];
                                        $this->modelArmy->destroyArmy($army['armyId'], $this->playerId);
                                        $victory = false;
                                    }
                                }
                            } else {
                                new Game_Logger('nie Dojdę');
                                $modelArmy->zeroArmyMovesLeft($armyId, $this->playerId);
                                break;
                            }
                        }
                    }
                }
            }
        } else {
            new Game_Logger('Zamek pusty');
        }
    }

    private function isEnemyStronger($army, $enemy=null, $castleId=null, $modelCastle=null) {
        $attackerCount = 0;
        if ($enemy && isset($enemy['position'])) {
            $position = $this->modelArmy->convertPosition($enemy['position']);
        }
        $battle = new Game_Battle($army, $enemy);
        if ($castleId) {
            $battle->addCastleDefenseModifier(Application_Model_Board::getCastleDefense($castleId) + $modelCastle->getCastleDefenseModifier($castleId));
        }
        if (isset($position)) {
            $battle->addTowerDefenseModifier($position[0], $position[1]);
        }
        $battle->fight();
        if ($battle->getAttacker()) {
            $attackerCount++;
        }
        $battle = new Game_Battle($army, $enemy);
        if ($castleId) {
            $battle->addCastleDefenseModifier(Application_Model_Board::getCastleDefense($castleId) + $modelCastle->getCastleDefenseModifier($castleId));
        }
        if (isset($position)) {
            $battle->addTowerDefenseModifier($position[0], $position[1]);
        }
        $battle->fight();
        if ($battle->getAttacker()) {
            $attackerCount++;
        }
        new Game_Logger('attackerCount=>'.$attackerCount);
        if ($attackerCount) {
            return false;
        }
        return true;
    }

    private function getClosestEnemyArmy($fields, $position, $army, $enemies) {

    }

    public function getClosestEnemyCastle($castlesAndFields, $position) {
        $heuristics = array();
        $canFlySwim = $this->modelArmy->getArmyCanFlySwim($this->army);
        $i = 0;
        $srcX = $position[0] / 40;
        $srcY = $position[1] / 40;
        $paths = array();
        $bingo = false;
        $castles = $castlesAndFields['hostileCastles'];
        $fields = $castlesAndFields['fields'];

        foreach ($castles as $castleId => $castle) {
            $aStar = new Game_Astar($castle['position']['x'], $castle['position']['y']);
            $heuristics[$castleId] = $aStar->calculateH($position[0], $position[1]);
        }
        unset($castleId);
        asort($heuristics, SORT_NUMERIC);

        foreach ($heuristics as $castleId => $v) {
            $i++;
            if ($i > 4) {
                break;
            }
            $position = Application_Model_Board::getCastlePosition($castleId);
            $destX = $position['x'] / 40;
            $destY = $position['y'] / 40;
            $fields = Application_Model_Board::changeCasteFields($fields, $destX, $destY, 'c');
            $aStar = new Game_Astar($destX, $destY);
            $aStar->start($srcX, $srcY, $fields, $canFlySwim['canFly'], $canFlySwim['canSwim']);
            $paths[$castleId] = $aStar->getFullPathMovesSpend($destX . '_' . $destY);
            $fields = Application_Model_Board::changeCasteFields($fields, $destX, $destY, 'e');
            if ($paths[$castleId] < $this->army['movesLeft']) {
                $this->path = $aStar->restorePath($destX . '_' . $destY, $this->army['movesLeft']);
                $this->currentPosition = $aStar->getCurrentPosition();
                if ($this->currentPosition['movesSpend'] <= $this->army['movesLeft']) {
                    $bingo = true;
                    break;
                }
            }
        }
        if (!$bingo) {
            asort($paths, SORT_NUMERIC);
            foreach ($paths as $castleId => $v) {
                if ($v) {
                    break;
                }
            }
            $position = Application_Model_Board::getCastlePosition($castleId);
            $destX = $position['x'] / 40;
            $destY = $position['y'] / 40;
            $fields = Application_Model_Board::changeCasteFields($fields, $destX, $destY, 'c');
            $aStar = new Game_Astar($destX, $destY);
            $aStar->start($srcX, $srcY, $fields, $canFlySwim['canFly'], $canFlySwim['canSwim']);
            $this->path = $aStar->restorePath($destX . '_' . $destY, $this->army['movesLeft']);
            $this->currentPosition = $aStar->getCurrentPosition();
        }
        return $castleId;
    }

}

