<?php

class ComputerController extends Game_Controller_Action {

    private $modelGame;
    private $modelArmy;
    private $playerId;

    public function _init() {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
        $this->modelGame = new Application_Model_Game($this->_namespace->gameId);
        $this->modelArmy = new Application_Model_Army($this->_namespace->gameId);
    }

    public function indexAction() {
        // action body
        if (!$this->modelGame->isGameMaster($this->_namespace->player['playerId'])) {
            throw new Exception('Nie Twoja gra!');
        }
        $this->playerId = $this->modelGame->getTurnPlayerId();
        $modelPlayer = new Application_Model_Player(null, false);
        if (!$modelPlayer->isComputer($this->playerId)) {
            throw new Exception('To nie komputer!');
        }
        if (!$this->modelGame->playerTurnActive($this->playerId)) {
            $this->startTurn();
        } else {
            $army = $this->modelArmy->getComputerArmyToMove($this->playerId);
            if (!empty($army['armyId'])) {
                $this->moveArmy($army);
            } else {
                $this->endTurn();
            }
        }
    }

    private function firstBlock($enemies, $army, $castlesAndFields) {
        $namespace = Game_Namespace::getNamespace();
        $modelCastle = new Application_Model_Castle($namespace->gameId);
        if (!$modelCastle->enemiesCastlesExist($this->playerId)) {
            $this->secondBlock($enemies, $army, $castlesAndFields);
        } else {
            $castleId = Game_Computer::getWeakerEnemyCastle($castlesAndFields['hostileCastles'], $army, $this->playerId);
            if ($castleId) {
                $range = Game_Computer::isCastleInRange($castlesAndFields, $castleId, $army);
//                 new Game_Logger($range);
                if ($range['in']) {
                    //atakuj
                    $fightEnemy = Game_Computer::fightEnemy($army, null, $this->playerId, $castleId);
                    $this->endMove($fightEnemy['currentPosition'], $army['armyId'], $fightEnemy['path'], $fightEnemy['battle'], $fightEnemy['victory'], $castleId);
                } else {
                    $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $range['currentPosition']);
                    $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
                    $this->endMove($range['currentPosition'], $army['armyId'], $range['path']);
                }
            } else {
                $this->secondBlock($enemies, $army, $castlesAndFields);
            }
        }
    }

    private function secondBlock($enemies, $army, $castlesAndFields) {
        if (!$enemies) {
            throw new Exception('Wygrałem!?');
        } else {
            foreach ($enemies as $enemy) {
                $castleId = Application_Model_Board::isArmyInCastle($enemy['x'], $enemy['y'], $castlesAndFields['castles']);
                if (Game_Computer::isEnemyStronger($army, $enemy, $castleId)) {
                    continue;
                } else {
                    break;
                }
            }
            if (isset($enemy)) {
                //atakuj
                $fightEnemy = Game_Computer::fightEnemy($army, $enemy, $this->playerId, $castleId);
                $this->endMove($fightEnemy['currentPosition'], $army['armyId'], $fightEnemy['path'], $fightEnemy['battle'], $fightEnemy['victory'], $castleId);
            } else {
                $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
                $this->endMove(array('x' => $army['x'], 'y' => $army['y']), $army['armyId']);
            }
        }
    }

    private function moveArmy($army) {
        $canFlySwim = $this->modelArmy->getArmyCanFlySwim($army);
        $army['canFly'] = $canFlySwim['canFly'];
        $army['canSwim'] = $canFlySwim['canSwim'];
        $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
        $computer = new Game_Computer();
        $myCastles = $modelCastle->getPlayerCastles($this->playerId);
        $myCastleId = Application_Model_Board::isArmyInCastle($army['x'], $army['y'], $myCastles);
        $fields = $this->modelArmy->getEnemyArmiesFieldsPositions($this->playerId);
        $razed = $modelCastle->getRazedCastles();
        $castlesAndFields = Application_Model_Board::prepareCastlesAndFields($fields, $razed, $myCastles);
        $enemies = $this->modelArmy->getAllEnemiesArmies($this->playerId);

        if ($myCastleId !== null) {
            $castlePosition = Application_Model_Board::getCastlePosition($myCastleId);
            $enemiesHaveRange = $computer->canEnemyReachThisCastle($castlePosition, $castlesAndFields, $enemies);
            $enemiesInRange = $computer->getEnemiesInRange($enemies, $army);
            if (!$enemiesHaveRange) {
                if (!$enemiesInRange) {
                    if (empty($army['heroes'])) {
                        $this->firstBlock($enemies, $army, $castlesAndFields);
                    } else {
                        $modelRuin = new Application_Model_Ruin($this->_namespace->gameId);
                        $ruin = $computer->getNearestRuin($castlesAndFields['fields'], $modelRuin->getFull(), $army);
                        if (!$ruin) {
                            $this->firstBlock($enemies, $army, $castlesAndFields);
                        } else {
                            //idź do ruin
                            $data = array(
                                'position' => $ruin['x'] . ',' . $ruin['y'],
                                'movesSpend' => $ruin['movesSpend']
                            );
                            $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $data);
                            $namespace = Game_Namespace::getNamespace();
                            $modelRuin = new Application_Model_Ruin($namespace->gameId);
                            $modelRuin->searchRuin($this->modelArmy, $army['heroes'][0]['heroId'], $army['armyId'], $this->playerId);
                            $this->endMove(array('x' => $ruin['x'], 'y' => $ruin['y']), $army['armyId'], $ruin['path']);
                        }
                    }
                } else {
                    foreach ($enemiesInRange as $enemy) {
                        $castleId = Application_Model_Board::isArmyInCastle($enemy['x'], $enemy['y'], $castlesAndFields['castles']);
                        if (Game_Computer::isEnemyStronger($army, $enemy, $castleId)) {
                            continue;
                        } else {
                            break;
                        }
                    }
                    if (isset($enemy)) {
                        //atakuj
                        $fightEnemy = Game_Computer::fightEnemy($army, $enemy, $this->playerId, $castleId);
                        $this->endMove($fightEnemy['currentPosition'], $army['armyId'], $fightEnemy['path'], $fightEnemy['battle'], $fightEnemy['victory'], $castleId);
                    } else {
                        $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
                        $this->endMove(array('x' => $army['x'], 'y' => $army['y']), $army['armyId']);
                    }
                }
            } else {
                if (!$enemiesInRange) {
                    $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
                    $this->endMove(array('x' => $army['x'], 'y' => $army['y']), $army['armyId']);
                } else {
                    if (count($enemiesHaveRange) > count($enemiesInRange)) {
                        $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
                        $this->endMove(array('x' => $army['x'], 'y' => $army['y']), $army['armyId']);
                    } else {
                        if (!$computer->canAttackAllEnemyHaveRange()) {
                            $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
                            $this->endMove(array('x' => $army['x'], 'y' => $army['y']), $army['armyId']);
                        } else {
                            //atakuj
                        }
                    }
                }
            }
        } else {
            $myEmptyCastle = $computer->getMyEmptyCastleInMyRange();
            if (!$myEmptyCastle) {
                $this->firstBlock($enemies, $army, $castlesAndFields);
            } else {
                if (!$computer->isMyCastleInRangeOfEnemy($myEmptyCastle)) {
                    $this->firstBlock($enemies, $army, $castlesAndFields);
                } else {
                    //idź do zamku
                    $data = array(
                        'position' => $myEmptyCastle['position']['x'] . ',' . $myEmptyCastle['position']['y'],
                        'movesSpend' => $army['movesLeft']
                    );
                    $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $data);
                    $this->endMove($myEmptyCastle['position'], $army['armyId']);
                }
            }
        }
    }

    private function endMove($position, $oldArmyId, $path = null, $battle = null, $victory = false, $castleId = null) {
        $armyId = $this->modelArmy->joinArmiesAtPosition($position, $this->playerId);
        if ($armyId) {
            $result = $this->modelArmy->getArmyByArmyIdPlayerId($armyId, $this->playerId);
        } else {
            $result = array();
        }
        $result['action'] = 'continue';
        $result['oldArmyId'] = $oldArmyId;
        if ($castleId) {
            $result['castleId'] = $castleId;
        }
        if (!empty($path)) {
            $result['path'] = $path;
        }
        $result['victory'] = $victory;
        if (!empty($battle)) {
            $result['battle'] = $battle;
        }
        $this->view->response = Zend_Json::encode($result);
    }

    private function endTurn() {
        $youWin = false;
        $response = array();
        $nextPlayer = array(
            'color' => $this->modelGame->getPlayerColor($this->playerId)
        );
        while (empty($response)) {
            $nextPlayer = $this->modelGame->nextTurn($nextPlayer['color']);
            $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
            $playerCastlesExists = $modelCastle->playerCastlesExists($nextPlayer['playerId']);
            $playerArmiesExists = $this->modelArmy->playerArmiesExists($nextPlayer['playerId']);
            if ($playerCastlesExists || $playerArmiesExists) {
                $response = $nextPlayer;
                if ($nextPlayer['playerId'] == $this->playerId) {
                    $youWin = true;
                    $this->modelGame->endGame();
                } else {
                    $nr = $this->modelGame->updateTurnNumber($nextPlayer['playerId']);
                    if ($nr) {
                        $response['nr'] = $nr;
                    }
                    $modelCastle->raiseAllCastlesProductionTurn($this->playerId);
                }
                $response['win'] = $youWin;
            } else {
                $this->modelGame->setPlayerLostGame($nextPlayer['playerId']);
            }
        }
        $response['action'] = 'end';
        $this->view->response = Zend_Json::encode($response);
    }

    private function startTurn() {
        $this->modelGame->turnActivate($this->playerId);
        $castles = array();
        $this->modelArmy->resetHeroesMovesLeft($this->playerId);
        $this->modelArmy->resetSoldiersMovesLeft($this->playerId);
        $gold = $this->modelGame->getPlayerInGameGold($this->playerId);
        $income = 0;
        $costs = 0;
        $turnNumber = $this->modelGame->getTurnNumber();
        if ($turnNumber > 0) {
            $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
            $castlesId = $modelCastle->getPlayerCastles($this->playerId);
            foreach ($castlesId as $id) {
                $castleId = $id['castleId'];
                $castles[$castleId] = Application_Model_Board::getCastle($castleId);
                $castle = $castles[$castleId];
                $income += $castle['income'];
                $castleProduction = $modelCastle->getCastleProduction($castleId, $this->playerId);
                if ($turnNumber < 10) {
                    $unitName = Application_Model_Board::getMinProductionTimeUnit($castleId);
                } else {
                    $unitName = Application_Model_Board::getCastleOptimalProduction($castleId);
                }
                $modelUnit = new Application_Model_Unit();
                $unitId = $modelUnit->getUnitIdByName($unitName);
                if ($unitId != $castleProduction['production']) {
                    $modelCastle->setCastleProduction($castleId, $unitId, $this->playerId);
                    $castleProduction = $modelCastle->getCastleProduction($castleId, $this->playerId);
                }
                $castles[$castleId]['productionTurn'] = $castleProduction['productionTurn'];
                $unitName = Application_Model_Board::getUnitName($castleProduction['production']);
                if ($castle['production'][$unitName]['time'] <= $castleProduction['productionTurn'] AND $castle['production'][$unitName]['cost'] <= $gold) {
                    if ($modelCastle->resetProductionTurn($castleId, $this->playerId) == 1) {
                        $armyId = $this->modelArmy->getArmyIdFromPosition($castle['position']);
                        if (!$armyId) {
                            $armyId = $this->modelArmy->createArmy($castle['position'], $this->playerId);
                        }
                        $this->modelArmy->addSoldierToArmy($armyId, $castleProduction['production'], $this->playerId);
                    }
                }
            }
            if (isset($castle['position'])) {
                $gold = $this->handleHeroResurrection($gold, $castle['position']);
            }
            $armies = $this->modelArmy->getPlayerArmies($this->playerId);
            if (empty($castles) && empty($armies)) {
                $this->view->response = Zend_Json::encode(array('action' => 'gameover'));
            } else {
                foreach ($armies as $k => $army) {
                    foreach ($army['soldiers'] as $unit) {
                        $costs += $unit['cost'];
                    }
                }
                $gold = $gold + $income - $costs;
                $this->modelGame->updatePlayerInGameGold($this->playerId, $gold);

                $this->view->response = Zend_Json::encode(array('action' => 'continue'));
            }
        }
    }

    private function handleHeroResurrection($gold, $position) {
        if (!$this->modelArmy->isHeroInGame($this->playerId)) {
            $this->modelArmy->connectHero($this->playerId);
        }
        $heroId = $this->modelArmy->getDeadHeroId($this->playerId);
        if ($heroId) {
            if ($gold >= 100) {
                $armyId = $this->modelArmy->heroResurection($heroId, $position, $this->playerId);
                if ($armyId) {
                    return $gold - 100;
                }
            }
        }
        return $gold;
    }

}

