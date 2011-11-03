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

    private function moveArmy($army) {
        $modelCastle= new Application_Model_Castle($this->_namespace->gameId);
        $computer = new Game_Computer($this->playerId, $army, $this->modelArmy);
        $position = $this->modelArmy->convertPosition($army['position']);
        $myCastles = $modelCastle->getPlayerCastles($this->playerId);
        $myCastleId = Application_Model_Board::isArmyInCastle($position, $myCastles);
        $fields = $this->modelArmy->getEnemyArmiesFieldsPositions($this->playerId);
        $razed = $modelCastle->getRazedCastles();
        $castlesAndFields = Application_Model_Board::prepareCastlesAndFields($fields, $razed, $myCastles);
        if ($myCastleId !== null) {
            $computer->handleEnemyIsNearCastle(Application_Model_Board::getCastlePosition($myCastleId), $this->modelArmy, $castlesAndFields, $modelCastle);
        }else{
            $castleId = $computer->getClosestEnemyCastle($castlesAndFields, $position);
        }
        $currentPosition = $computer->getCurrentPosition();
        if ($currentPosition) {
            if ($castleId) {
                $this->movesSpend = $currentPosition['movesSpend'];
                if (Application_Model_Board::isCastleFild($currentPosition, Application_Model_Board::getCastlePosition($castleId))) {
                    $this->victory = $computer->fightCastle($modelCastle, $castleId);
                    $this->inCastle = true;
                } else {
                    $this->inCastle = false;
                }
            }
        }
        $currentPosition = $computer->getCurrentPosition();
        if (!$currentPosition) {
            $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
            $this->view->response = Zend_Json::encode(array('action' => 'continue'));
            return null;
        }
        $castleId = $computer->getCastleId();
        $inCastle = $computer->getInCastle();
        $path = $computer->getPath();
        $victory = $computer->getVictory();
        $battle = $computer->getBattle();
        $data = array(
            'position' => $currentPosition['x'] . ',' . $currentPosition['y'],
            'movesSpend' => $computer->getMovesSpend()
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
                $result['action'] = 'continue';
                $result['oldArmyId'] = $army['armyId'];
                $result['castleId'] = $castleId;
                $result['in'] = $inCastle;
                if (!empty($path)) {
                    $result['path'] = $path;
                }
                $result['victory'] = $victory;
                if (!empty($battle)) {
                    $result['battle'] = $battle;
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

