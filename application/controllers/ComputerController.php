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

    private function firstBlock($enemies, $army, $castlesAndFields, $myCastles) {
        $namespace = Game_Namespace::getNamespace();
        $modelCastle = new Application_Model_Castle($namespace->gameId);
        if (!$modelCastle->enemiesCastlesExist($this->playerId)) {
            new Game_Logger('BRAK ZAMKÓW WROGA');
            $this->secondBlock($enemies, $army, $castlesAndFields, $myCastles);
        } else {
            new Game_Logger('SĄ ZAMKI WROGA');
            $castleId = Game_Computer::getWeakerEnemyCastle($castlesAndFields['hostileCastles'], $army, $this->playerId);
            if ($castleId !== null) {
                new Game_Logger('JEST SŁABSZY ZAMEK WROGA');
                $castleRange = Game_Computer::isEnemyCastleInRange($castlesAndFields, $castleId, $army);
                if ($castleRange['in']) {
                    //atakuj
                    new Game_Logger('SŁABSZY ZAMEK WROGA W ZASIĘGU - ATAKUJ!');
                    $fightEnemy = Game_Computer::fightEnemy($army, null, $this->playerId, $castleId);
                    $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $castleRange['currentPosition']);
                    $this->endMove($army['armyId'], $castleRange['currentPosition'], $castleRange['path'], $fightEnemy['battle'], $fightEnemy['victory'], $castleId);
                } else {
                    new Game_Logger('SŁABSZY ZAMEK WROGA POZA ZASIĘGIEM');
                    $enemy = Game_Computer::getWeakerEnemyArmyInRange($enemies, $army, $castlesAndFields);
                    if ($enemy) {
                        //atakuj
                        new Game_Logger('JEST SŁABSZA ARMIA WROGA W ZASIĘGU');
                        $fightEnemy = Game_Computer::fightEnemy($army, $enemy, $this->playerId, $enemy['castleId']);
                        $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $enemy['currentPosition']);
                        $this->endMove($army['armyId'], $enemy['currentPosition'], $enemy['path'], $fightEnemy['battle'], $fightEnemy['victory'], $enemy['castleId'], null, $enemy['armyId']);
                    } else {
                        new Game_Logger('BRAK SŁABSZEJ ARMII WROGA W ZASIĘGU');
                        $enemy = Game_Computer::getStrongerEnemyArmyInRange($enemies, $army, $castlesAndFields);
                        if ($enemy) {
                            new Game_Logger('JEST SILNIEJSZA ARMIA WROGA W ZASIĘGU');
                            $join = Game_Computer::getMyArmyInRange($army, $castlesAndFields['fields']);
                            if ($join) {
                                new Game_Logger('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                                $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $join['currentPosition']);
                                $this->endMove($army['armyId'], $join['currentPosition'], $join['path']);
                            } else {
                                new Game_Logger('BRAK MOJEJ ARMII W ZASIĘGU - IDŹ DO ZAMKU!');
                                $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $castleRange['currentPosition']);
                                $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
                                $this->endMove($army['armyId'], $castleRange['currentPosition'], $castleRange['path']);
                            }
                        } else {
                            new Game_Logger('BRAK SILNIEJSZEJ ARMII WROGA W ZASIĘGU - IDŹ DO ZAMKU!');
                            $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $castleRange['currentPosition']);
                            $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
                            $this->endMove($army['armyId'], $castleRange['currentPosition'], $castleRange['path']);
                        }
                    }
                }
            } else {
                new Game_Logger('BRAK SŁABSZYCH ZAMKÓW WROGA');
                $this->secondBlock($enemies, $army, $castlesAndFields, $myCastles);
            }
        }
    }

    private function secondBlock($enemies, $army, $castlesAndFields, $myCastles) {
        if (!$enemies) {
            throw new Exception('Wygrałem!?');
        } else {
            foreach ($enemies as $e) {
                $castleId = Application_Model_Board::isArmyInCastle($e['x'], $e['y'], $castlesAndFields['hostileCastles']);
                if (null !== $castleId) {
                    continue;
                }
                if (Game_Computer::isEnemyStronger($army, $e, $castleId)) {
                    continue;
                } else {
                    $enemy = $e;
                    break;
                }
            }
            if (isset($enemy)) {
                //atakuj
                new Game_Logger('WRÓG JEST SŁABSZY');
                $range = Game_Computer::isEnemyArmyInRange($castlesAndFields, $enemy, $army);
                if ($range['in']) {
                    new Game_Logger('SŁABSZY WRÓG W ZASIĘGU - ATAKUJ!');
                    $fightEnemy = Game_Computer::fightEnemy($army, $enemy, $this->playerId, $range['castleId']);
                    $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $range['currentPosition']);
                    $this->endMove($army['armyId'], $range['currentPosition'], $range['path'], $fightEnemy['battle'], $fightEnemy['victory']);
                } else {
                    new Game_Logger('SŁABSZY WRÓG POZA ZASIĘGIEM - IDŹ DO WROGA');
                    $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $range['currentPosition']);
                    $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
                    $this->endMove($army['armyId'], $range['currentPosition'], $range['path']);
                }
            } else {
                new Game_Logger('WRÓG JEST SILNIEJSZY');
                $join = Game_Computer::getMyArmyInRange($army, $castlesAndFields['fields']);
                if ($join) {
                    new Game_Logger('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                    $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $join['currentPosition']);
                    $this->endMove($army['armyId'], $join['currentPosition'], $join['path']);
                } else {
                    new Game_Logger('BRAK MOJEJ ARMII W ZASIĘGU');
                    $castle = Game_Computer::getMyCastelNearEnemy($enemies, $army, $castlesAndFields['fields'], $myCastles);
                    if($castle){
                        new Game_Logger('JEST MÓJ ZAMEK W POBLIŻU WROGA - IDŹ DO ZAMKU');
                        $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $castle['currentPosition']);
                        $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
                        $this->endMove($army['armyId'], $castle['currentPosition'], $castle['path']);
                    }else{
                        new Game_Logger('NIE MA MOJEGO ZAMKU W POBLIŻU WROGA - ZOSTAŃ');
                        $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
                        $this->endMove($army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    }
                }
            }
        }
    }

    private function ruinBlock($enemies, $army, $castlesAndFields, $myCastles) {
        if (empty($army['heroes'])) {
            new Game_Logger('BRAK HEROSA');
            $this->firstBlock($enemies, $army, $castlesAndFields, $myCastles);
        } else {
            new Game_Logger('JEST HEROS');
            new Game_Logger($army['heroes'], 'HEROS:');
            $modelRuin = new Application_Model_Ruin($this->_namespace->gameId);
            $ruin = Game_Computer::getNearestRuin($castlesAndFields['fields'], $modelRuin->getFull(), $army);
            if (!$ruin) {
                new Game_Logger('BRAK RUIN');
                $this->firstBlock($enemies, $army, $castlesAndFields, $myCastles);
            } else {
                //idź do ruin
                new Game_Logger('IDŹ DO RUIN');
                $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $ruin['currentPosition']);
                $modelRuin->addRuin($ruin['ruinId']);
                $modelRuin->searchRuin($army['heroes'][0]['heroId'], $army['armyId'], $this->playerId);
                $this->endMove($army['armyId'], $ruin['currentPosition'], $ruin['path'], null, false, null, $ruin['ruinId']);
            }
        }
    }

    private function moveArmy($army) {
        new Game_Logger('');
        new Game_Logger($army['armyId'], 'armyId:');
        $canFlySwim = $this->modelArmy->getArmyCanFlySwim($army);
        $army['canFly'] = $canFlySwim['canFly'];
        $army['canSwim'] = $canFlySwim['canSwim'];
        $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
        $myCastles = $modelCastle->getPlayerCastles($this->playerId);
        $myCastleId = Application_Model_Board::isArmyInCastle($army['x'], $army['y'], $myCastles);
        $fields = $this->modelArmy->getEnemyArmiesFieldsPositions($this->playerId);
        $razed = $modelCastle->getRazedCastles();
        $castlesAndFields = Application_Model_Board::prepareCastlesAndFields($fields, $razed, $myCastles);
        $enemies = $this->modelArmy->getAllEnemiesArmies($this->playerId);

        if ($myCastleId !== null) {
            new Game_Logger('W ZAMKU');
            $castlePosition = Application_Model_Board::getCastlePosition($myCastleId);
            $enemiesHaveRange = Game_Computer::canEnemyReachThisCastle($castlePosition, $castlesAndFields, $enemies);
            $enemiesInRange = Game_Computer::getEnemiesInRange($enemies, $army, $castlesAndFields['fields']);
            if (!$enemiesHaveRange) {
                new Game_Logger('BRAK WROGA Z ZASIĘGIEM');
                if (!$enemiesInRange) {
                    new Game_Logger('BRAK WROGA W ZASIĘGU');
                    $this->ruinBlock($enemies, $army, $castlesAndFields, $myCastles);
                } else {
                    new Game_Logger('JEST WRÓG W ZASIĘGU');
                    foreach ($enemiesInRange as $e) {
                        $castleId = Application_Model_Board::isArmyInCastle($e['x'], $e['y'], $castlesAndFields['hostileCastles']);
                        if (Game_Computer::isEnemyStronger($army, $e, $castleId)) {
                            continue;
                        } else {
                            $enemy = $e;
                            break;
                        }
                    }
                    if (isset($enemy)) {
                        new Game_Logger('WRÓG JEST SŁABSZY - ATAKUJ!');
                        //atakuj
                        if ($castleId !== null) {
                            $range = Game_Computer::isEnemyCastleInRange($castlesAndFields, $castleId, $army);
                        } else {
                            $range = Game_Computer::isEnemyArmyInRange($castlesAndFields, $enemy, $army);
                        }
                        $fightEnemy = Game_Computer::fightEnemy($army, $enemy, $this->playerId, $castleId);
                        $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $range['currentPosition']);
                        $this->endMove($army['armyId'], $range['currentPosition'], $range['path'], $fightEnemy['battle'], $fightEnemy['victory'], $castleId);
                    } else {
                        new Game_Logger('WRÓG JEST SILNIEJSZY - ZOSTAŃ!');
                        $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
                        $this->endMove($army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    }
                }
            } else {
                new Game_Logger('JEST WRÓG Z ZASIĘGIEM');
                if (!$enemiesInRange) {
                    new Game_Logger('BRAK WROGA W ZASIĘGU - ZOSTAŃ!');
                    $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
                    $this->endMove($army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                } else {
                    new Game_Logger('JEST WRÓG W ZASIĘGU');
                    if (count($enemiesHaveRange) > count($enemiesInRange)) {
                        new Game_Logger('WRÓGÓW Z ZASIĘGIEM > WRÓGÓW W ZASIĘGU - ZOSTAŃ!');
                        $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
                        $this->endMove($army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    } else {
                        new Game_Logger('WRÓGÓW Z ZASIĘGIEM <= WRÓGÓW W ZASIĘGU');
                        $enemy = Game_Computer::canAttackAllEnemyHaveRange($enemiesHaveRange, $army, $castlesAndFields['hostileCastles']);
                        if (!$enemy) {
                            new Game_Logger('NIE MOGĘ ZAATAKOWAĆ WRÓGÓW Z ZASIĘGIEM - ZOSTAŃ!');
                            $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
                            $this->endMove($army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                        } else {
                            //atakuj
                            new Game_Logger('ATAKUJĘ WRÓGÓW Z ZASIĘGIEM - ATAKUJ!'); //atakuję wrogów którzy mają zasięg na zamek, brak enemy armyId, armia nie zmienia pozycji
                            $aStar = $enemy['aStar'];
                            $aStar->restorePath($enemy['key'], $enemy['movesToSpend']);
                            $path = $aStar->reversePath();
                            $currentPosition = $aStar->getCurrentPosition();
                            $fightEnemy = Game_Computer::fightEnemy($army, $enemy, $this->playerId, $enemy['castleId']);
                            $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $currentPosition);
                            $this->endMove($army['armyId'], $currentPosition, $path, $fightEnemy['battle'], $fightEnemy['victory'], $enemy['castleId'], null, $enemy['armyId']);
                        }
                    }
                }
            }
        } else {
            new Game_Logger('POZA ZAMKIEM');
            $myEmptyCastle = Game_Computer::getMyEmptyCastleInMyRange($myCastles, $army, $castlesAndFields['fields']);
            if (!$myEmptyCastle) {
                new Game_Logger('NIE MA PUSTEGO ZAMKU W ZASIĘGU');
                $this->ruinBlock($enemies, $army, $castlesAndFields, $myCastles);
            } else {
                new Game_Logger('JEST PUSTY ZAMEK W ZASIĘGU');
                if (!Game_Computer::isMyCastleInRangeOfEnemy($enemies, $myEmptyCastle, $castlesAndFields['fields'])) {
                    new Game_Logger('WRÓG NIE MA ZASIĘGU NA PUSTY ZAMEK');
                    $this->firstBlock($enemies, $army, $castlesAndFields, $myCastles);
                } else {
                    //idź do zamku
                    new Game_Logger('WRÓG MA ZASIĘG NA PUSTY ZAMEK - IDŹ DO ZAMKU!');
                    $data = array(
                        'x' => $myEmptyCastle['x'],
                        'y' => $myEmptyCastle['y'],
                        'movesSpend' => $army['movesLeft']
                    );
                    $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $data);
                    $this->endMove($army['armyId'], $myEmptyCastle['currentPosition'], $myEmptyCastle['path']);
                }
            }
        }
    }

    private function endMove($oldArmyId, $position, $path = null, $battle = null, $victory = false, $castleId = null, $ruinId = null, $enemyArmyId = null) {
        $armyId = $this->modelArmy->joinArmiesAtPosition($position, $this->playerId);
        if (!$armyId) {
            $armyId = $oldArmyId;
        }
        $result = $this->modelArmy->getArmyByArmyIdPlayerId($armyId, $this->playerId);

        $result['action'] = 'continue';
        $result['oldArmyId'] = $oldArmyId;
        if ($castleId !== null) {
            $result['castleId'] = $castleId;
        }
        if ($ruinId !== null) {
            $result['ruinId'] = $ruinId;
        }
        if ($enemyArmyId) {
            $result['enemyArmyId'] = $enemyArmyId;
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

