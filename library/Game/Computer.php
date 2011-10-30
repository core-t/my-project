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

    public function __construct($playerId, $army, $modelArmy, $modelCastle, $castlesSchema) {
        $this->playerId = $playerId;
        $this->army = $army;
        $this->modelArmy = $modelArmy;
        $position = $this->modelArmy->convertPosition($army['position']);
        $fields = $this->modelArmy->getEnemyArmiesFieldsPositions($this->playerId);
        $myCastles = $modelCastle->getPlayerCastles($this->playerId);
        $razed = $modelCastle->getRazedCastles();
//        if ($this->isArmyInCastle($position, $myCastles, $castlesSchema)) {
//            throw new Exception('ehe');
//        }
        $this->castleId = $this->getClosestEnemyCastle(Application_Model_Board::prepareCastlesAndFields($fields, $razed, $myCastles, $castlesSchema), $position, $castlesSchema);
//        Zend_Debug::dump($this->currentPosition);
//        Zend_Debug::dump($this->path);
        if ($this->currentPosition) {
            if ($this->castleId) {
//        $enemies = $this->modelArmy->getAllEnemiesArmies($this->playerId);
//        $closestEnemyArmy = $this->getClosestEnemyArmy($fields, $position, $army, $enemies);
                $this->movesSpend = $this->currentPosition['movesSpend'];
                if (Application_Model_Board::isCastleFild($this->currentPosition, $castlesSchema[$this->castleId]['position'])) {
                    $this->victory = $this->fightCastle($modelCastle, $castlesSchema);
                    $this->inCastle = true;
                } else {
                    $this->inCastle = false;
                }
            }
        }
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

    private function fightCastle($modelCastle, $castlesSchema) {
        $victory = false;
        if (($this->army['movesLeft'] - $this->movesSpend) < 2) {
            $result = Game_Astar::rewindPathOutOfCastle($this->path, $this->currentPosition, $castlesSchema[$this->castleId]['position']);
            $this->path = $result['path'];
            $this->currentPosition = $result['currentPosition'];
            $this->modelArmy->zeroArmyMovesLeft($this->army['armyId'], $this->playerId);
        } else {
            $this->movesSpend += 2;
            if ($modelCastle->isEnemyCastle($this->castleId, $this->playerId)) {
                $battle = new Game_Battle($this->army, $this->modelArmy->getAllUnitsFromCastlePosition($castlesSchema[$this->castleId]['position']));
                $battle->addCastleDefenseModifier($castlesSchema[$this->castleId]['defensePoints'] + $modelCastle->getCastleDefenseModifier($this->castleId));
                $battle->fight();
                $battle->updateArmies();
                $this->battle = $battle->getResult();
                $enemy = $this->modelArmy->updateAllArmiesFromCastlePosition($castlesSchema[$this->castleId]['position']);
                if (empty($enemy)) {
                    $modelCastle->changeOwner($this->castleId, $this->playerId);
                    $victory = true;
                } else {
                    $result = Game_Astar::rewindPathOutOfCastle($this->path, $this->currentPosition, $castlesSchema[$this->castleId]['position']);
                    $this->path = $result['path'];
                    $this->currentPosition = $result['currentPosition'];
                    $this->modelArmy->destroyArmy($this->army['armyId'], $this->playerId);
                    $victory = false;
                }
            } else {
                $battle = new Game_Battle($this->army, null);
                $battle->fight();
                $battle->updateArmies();
                $this->battle = $battle->getResult();
                $defender = $battle->getDefender();
                if (empty($defender['soldiers'])) {
                    $modelCastle->addCastle($this->castleId, $this->playerId);
                    $victory = true;
                } else {
                    $result = Game_Astar::rewindPathOutOfCastle($this->path, $this->currentPosition, $castlesSchema[$this->castleId]['position']);
                    $this->path = $result['path'];
                    $this->currentPosition = $result['currentPosition'];
                    $this->modelArmy->destroyArmy($this->army['armyId'], $this->playerId);
                    $victory = false;
                }
            }
        }
        return $victory;
    }

    private function isEnemyStronger($army, $enemy=null, $castlesSchema=null, $castleId=null, $modelCastle=null) {
        $attackerCount = 0;
        if ($enemy) {
            $position = $this->modelArmy->convertPosition($enemy['position']);
        }
        $battle = new Game_Battle($army, $enemy);
        if ($castlesSchema && $castleId) {
            $battle->addCastleDefenseModifier($castlesSchema[$castleId]['defensePoints'] + $modelCastle->getCastleDefenseModifier($castleId));
        }
        if (isset($position)) {
            $battle->addTowerDefenseModifier($position[0], $position[1]);
        }
        $battle->fight();
        if ($battle->getAttacker()) {
            $attackerCount++;
        }
        $battle = new Game_Battle($army, $enemy);
        if ($castlesSchema && $castleId) {
            $battle->addCastleDefenseModifier($castlesSchema[$castleId]['defensePoints'] + $modelCastle->getCastleDefenseModifier($castleId));
        }
        if (isset($position)) {
            $battle->addTowerDefenseModifier($position[0], $position[1]);
        }
        $battle->fight();
        if ($battle->getAttacker()) {
            $attackerCount++;
        }
        if ($attackerCount) {
            return false;
        }
        return true;
    }

    private function getClosestEnemyArmy($fields, $position, $army, $enemies) {

    }

    private function getClosestEnemyCastle($castlesAndFields, $position, $castlesSchema) {
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
            $destX = $castlesSchema[$castleId]['position']['x'] / 40;
            $destY = $castlesSchema[$castleId]['position']['y'] / 40;
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
            $destX = $castlesSchema[$castleId]['position']['x'] / 40;
            $destY = $castlesSchema[$castleId]['position']['y'] / 40;
            $fields = Application_Model_Board::changeCasteFields($fields, $destX, $destY, 'c');
            $aStar = new Game_Astar($destX, $destY);
            $aStar->start($srcX, $srcY, $fields, $canFlySwim['canFly'], $canFlySwim['canSwim']);
            $this->path = $aStar->restorePath($destX . '_' . $destY, $this->army['movesLeft']);
            $this->currentPosition = $aStar->getCurrentPosition();
        }
        return $castleId;
    }

}

