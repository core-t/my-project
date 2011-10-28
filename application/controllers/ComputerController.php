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
//        $this->startTurn();
    }

    private function moveArmy($army) {
        $position = $this->modelArmy->convertPosition($army['position']);
        $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
        $modelBoard = new Application_Model_Board();
        $fields = $this->modelArmy->getEnemyArmiesFieldsPositions($this->playerId);
        $castlesSchema = $modelBoard->getCastlesSchema();
        $myCastles = $modelCastle->getPlayerCastles($this->playerId);
        $razed = $modelCastle->getRazedCastles();
        if ($this->isArmyInCastle($position, $myCastles, $castlesSchema)) {
//            throw new Exception('ehe');
        }
        $data = $modelBoard->prepareCastlesAndFields($castlesSchema, $razed, $myCastles, $fields);
        $fields = $data[0];
        $castles = $data[1];
        $data = $this->getClosestEnemyCastle($castles, $castlesSchema, $fields, $position, $army);
        $currentPosition = $data[0];
        $path = $data[1];
        $castleId = $data[2];
        if (!$currentPosition) {
            $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
            $this->view->response = Zend_Json::encode(array('action' => 'continue'));
            return null;
        }
        $movesSpend = $currentPosition['movesSpend'];
        if (Application_Model_Board::isCastleFild($currentPosition, $castlesSchema[$castleId]['position'])) {
            $data = $this->fightCastle($movesSpend, $path, $currentPosition, $castlesSchema, $modelCastle, $army, $castleId);
            $path = $data['path'];
            $currentPosition = $data['currentPosition'];
            $victory = $data['victory'];
            $movesSpend = $data['movesSpend'];
            $battle = $data['battle'];
            $inCastle = true;
        } else {
            $inCastle = false;
        }
        $data = array(
            'position' => $currentPosition['x'] . ',' . $currentPosition['y'],
            'movesSpend' => $movesSpend
        );
        $res = $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $data);
        switch ($res) {
            case 1:
                $armyId = $this->modelArmy->joinArmiesAtPosition($data['position'], $this->playerId);
                if ($armyId) {
                    $result = $this->modelArmy->getArmyByArmyIdPlayerId($armyId, $this->playerId);
                } else {
                    $result = array();
                }
                if ($inCastle && $castleId) {
                    $this->handleEnemyIsNear($castlesSchema[$castleId]['position']);
                }
                $result['action'] = 'continue';
                $result['oldArmyId'] = $army['armyId'];
                $result['castleId'] = $castleId;
                $result['in'] = $inCastle;
                $result['path'] = $path;
                if (isset($victory)) {
                    $result['victory'] = $victory;
                }
                if (isset($battle) && is_object($battle)) {
                    $result['battle'] = $battle->getResult();
                }
                $this->view->response = Zend_Json::encode($result);
                break;
            case 0:
                throw new Exception('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                break;
            case null:
                throw new Exception('Zapytanie zwróciło błąd');
                break;
            default:
                throw new Exception('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                break;
        }
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
        $modelBoard = new Application_Model_Board();
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
                $castles[$castleId] = $modelBoard->getCastle($castleId);
                $castle = $castles[$castleId];
                $income += $castle['income'];
                $castleProduction = $modelCastle->getCastleProduction($castleId, $this->playerId);
                if ($turnNumber < 10) {
                    $unitName = $modelBoard->getMinProductionTimeUnit($castleId);
                } else {
                    $unitName = $modelBoard->getCastleOptimalProduction($castleId);
                }
                $modelUnit = new Application_Model_Unit();
                $unitId = $modelUnit->getUnitIdByName($unitName);
                if ($unitId != $castleProduction['production']) {
                    $modelCastle->setCastleProduction($castleId, $unitId, $this->playerId);
                    $castleProduction = $modelCastle->getCastleProduction($castleId, $this->playerId);
                }
                $castles[$castleId]['productionTurn'] = $castleProduction['productionTurn'];
                $unitName = $modelBoard->getUnitName($castleProduction['production']);
                if ($castle['production'][$unitName]['time'] <= $castleProduction['productionTurn'] AND $castle['production'][$unitName]['cost'] <= $gold) {
                    if ($modelCastle->resetProductionTurn($castleId, $this->playerId) == 1) {
                        $armyId = $this->modelArmy->getArmyIdFromPosition($castle['position']);
                        if (!$armyId) {
                            $armyId = $this->modelArmy->createArmy($castle['position'], $this->playerId);
                        }
                        $this->modelArmy->addSoldierToArmy($armyId, $castleProduction['production'], $this->playerId);
                    }
                }
                $this->handleEnemyIsNear($castle['position']);
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

    private function isArmyInCastle($position, $castles, $castlesSchema) {
        $aP = array(
            'x' => $position[0],
            'y' => $position[1]
        );
        foreach ($castles as $castle) {
            if (Application_Model_Board::isCastleFild($aP, $castlesSchema[$castle['castleId']]['position'])) {
                return true;
            }
        }
    }

    private function rewindPathOutOfCastle($path, $currentPosition, $castlePosition) {
        $oldPath = $path;
        $oldCurrentPosition = $currentPosition;
        $rewind = false;
        while (true) {
            if (!Application_Model_Board::isCastleFild($currentPosition, $castlePosition)) {
                $rewind = true;
                break;
            } else {
                $currentPosition = array_pop($path);
            }
        }
        if ($rewind) {
            return array($path, $currentPosition);
        } else {
            return array($oldPath, $oldCurrentPosition);
        }
    }

    private function getClosestEnemyCastle($castles, $castlesSchema, $fields, $position, $army) {
        $heuristics = array();
        $canFlySwim = $this->modelArmy->getArmyCanFlySwim($army);
        $i = 0;
        $srcX = $position[0] / 40;
        $srcY = $position[1] / 40;
        $paths = array();
        $bingo = false;

        foreach ($castles as $castleId => $castle) {
            $aStar = new Game_Astar($castle['position']['x'], $castle['position']['y']);
            $heuristics[$castleId] = $aStar->calculateH($position[0], $position[1]);
        }
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
            if ($paths[$castleId] < $army['movesLeft']) {
                $path = $aStar->restorePath($destX . '_' . $destY, $army['movesLeft']);
                $currentPosition = $aStar->getCurrentPosition();
                if ($currentPosition['movesSpend'] <= $army['movesLeft']) {
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
            $path = $aStar->restorePath($destX . '_' . $destY, $army['movesLeft']);
            $currentPosition = $aStar->getCurrentPosition();
        }
        return array($currentPosition, $path, $castleId);
    }

    private function fightCastle($movesSpend, $path, $currentPosition, $castlesSchema, $modelCastle, $army, $castleId) {
        $victory = false;
        $movesSpend += 2;
        $battle = null;
        if (($army['movesLeft'] - $movesSpend) < 0) {
            $rew = $this->rewindPathOutOfCastle($path, $currentPosition, $castlesSchema[$castleId]['position']);
            $path = $rew[0];
            $currentPosition = $rew[1];
        } else {
            if ($modelCastle->isEnemyCastle($castleId, $this->playerId)) {
                $enemy = $this->modelArmy->getAllUnitsFromCastlePosition($castlesSchema[$castleId]['position']);
                $battle = new Game_Battle($army, $enemy);
                $battle->addCastleDefenseModifier($castlesSchema[$castleId]['defensePoints'] + $modelCastle->getCastleDefenseModifier($castleId));
                $battle->fight();
                $battle->updateArmies();
                $enemy = $this->modelArmy->updateAllArmiesFromCastlePosition($castlesSchema[$castleId]['position']);
                if (empty($enemy)) {
                    $modelCastle->changeOwner($castleId, $this->playerId);
                    $victory = true;
                } else {
                    $rew = $this->rewindPathOutOfCastle($path, $currentPosition, $castlesSchema[$castleId]['position']);
                    $path = $rew[0];
                    $currentPosition = $rew[1];
                    $this->modelArmy->destroyArmy($army['armyId'], $this->playerId);
                    $victory = false;
                }
            } else {
                $battle = new Game_Battle($army, null);
                $battle->fight();
                $battle->updateArmies();
                $defender = $battle->getDefender();
                if (empty($defender['soldiers'])) {
                    $modelCastle->addCastle($castleId, $this->playerId);
                    $victory = true;
                } else {
                    $rew = $this->rewindPathOutOfCastle($path, $currentPosition, $castlesSchema[$castleId]['position']);
                    $path = $rew[0];
                    $currentPosition = $rew[1];
                    $this->modelArmy->destroyArmy($army['armyId'], $this->playerId);
                    $victory = false;
                }
            }
        }
        return array('path' => $path, 'currentPosition' => $currentPosition, 'victory' => $victory, 'movesSpend' => $movesSpend, 'battle' => $battle);
    }

    private function handleEnemyIsNear($castlePosition) {
        $armyIds = $this->modelArmy->getAllArmiesIdsFromCastlePosition($castlePosition);
        if ($armyIds) {
//            throw new Exception(Zend_Debug::dump($armyIds));
            $enemies = $this->modelArmy->getAllEnemiesArmies($this->playerId);
            $heuristics = array();
            $aaa = array();
            foreach ($enemies as $id => $enemy) {
                $position = $this->modelArmy->convertPosition($enemy['position']);
                $aStar = new Game_Astar($castlePosition['x'], $castlePosition['y']);
                $h = $aStar->calculateH($position[0], $position[1]);
                if ($h < ($enemy['numberOfMoves'] * 40)) {
                    $heuristics[$id] = array($h, $enemy['numberOfMoves'] * 40);
                } else {
                    $aaa[$id] = array($h, $enemy['numberOfMoves'] * 40);
                }
            }
//                    if ('KAZRACK' != $castle['name']) {
//            throw new Exception(Zend_Debug::dump($castle) . Zend_Debug::dump($aaa) . Zend_Debug::dump($heuristics));
//                    }
            if (!empty($heuristics)) {
                foreach ($armyIds as $armyId) {
//                    throw new Exception(Zend_Debug::dump($castlePosition) . Zend_Debug::dump($heuristics));
                    $this->modelArmy->zeroArmyMovesLeft($armyId, $this->playerId);
                }
            }
        }
    }

}

