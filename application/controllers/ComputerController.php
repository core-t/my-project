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
        $position = $this->modelArmy->convertPosition($army['position']);
        $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
        $modelBoard = new Application_Model_Board();
        $fields = $this->modelArmy->getEnemyArmiesFieldsPositions($this->playerId);
        $castlesSchema = $modelBoard->getCastlesSchema();
        $castles = array();
        foreach ($castlesSchema as $castleId => $castleSchema) {
            $x = $castleSchema['position']['x'] / 40;
            $y = $castleSchema['position']['y'] / 40;
            if ($modelCastle->isPlayerCastle($castleId, $this->playerId)) {
                $fields = $this->changeCasteFields($fields, $x, $y, 'c');
            } else {
                $castles[$castleId] = $castleSchema;
                $fields = $this->changeCasteFields($fields, $x, $y, 'e');
            }
        }
        $heuristics = array();
        foreach ($castles as $castleId => $castle) {
            $aStar = new Game_Astar($castle['position']['x'], $castle['position']['y']);
            $heuristics[$castleId] = $aStar->calculateH($position[0], $position[1]);
        }
        asort($heuristics, SORT_NUMERIC);
        $canFlySwim = $this->modelArmy->getArmyCanFlySwim($army);
        $i = 0;
        $srcX = $position[0] / 40;
        $srcY = $position[1] / 40;
        $paths = array();
        foreach ($heuristics as $castleId => $v) {
            $i++;
            if ($i > 4) {
                break;
            }
            $destX = $castlesSchema[$castleId]['position']['x'] / 40;
            $destY = $castlesSchema[$castleId]['position']['y'] / 40;
            $fields = $this->changeCasteFields($fields, $destX, $destY, 'c');
            $aStar = new Game_Astar($destX, $destY);
            $aStar->start($srcX, $srcY, $fields, $canFlySwim['canFly'], $canFlySwim['canSwim']);
            $paths[$castleId] = $aStar->getFullPathMovesSpend($destX . '_' . $destY);
            $fields = $this->changeCasteFields($fields, $destX, $destY, 'e');
        }
        asort($paths, SORT_NUMERIC);
        foreach ($paths as $castleId => $v) {
            if ($v) {
                break;
            }
        }
        $destX = $castlesSchema[$castleId]['position']['x'] / 40;
        $destY = $castlesSchema[$castleId]['position']['y'] / 40;
        $fields = $this->changeCasteFields($fields, $destX, $destY, 'c');
        $aStar = new Game_Astar($destX, $destY);
        $aStar->start($srcX, $srcY, $fields, $canFlySwim['canFly'], $canFlySwim['canSwim']);
        $path = $aStar->restorePath($destX . '_' . $destY, $army['movesLeft']);
        $currentPosition = $aStar->getCurrentPosition();
        if($this->isCastleFild($currentPosition, $castlesSchema[$castleId]['position'])){
            
        }
        $this->modelArmy->zeroArmyMovesLeft($army['armyId'], $this->playerId);
        if (!$currentPosition) {
            $this->view->response = Zend_Json::encode(array('action' => 'continue'));
            return null;
        }
//        throw new Exception(Zend_Debug::dump($currentPosition));
        $data = array(
            'position' => $currentPosition['x'] . ',' . $currentPosition['y'],
            'movesSpend' => $currentPosition['movesSpend']
        );
        $res = $this->modelArmy->updateArmyPosition($army['armyId'], $this->playerId, $data);
        switch ($res) {
            case 1:
                $armyId = $this->modelArmy->joinArmiesAtPosition($data['position'], $this->playerId);
                $result = $this->modelArmy->getArmyByArmyIdPlayerId($armyId, $this->playerId);
                $result['action'] = 'continue';
                $result['path'] = $path;
                $result['oldArmyId'] = $army['armyId'];
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
        if ($this->modelGame->getTurnNumber() > 0) {
            $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
            $castlesId = $modelCastle->getPlayerCastles($this->playerId);
            foreach ($castlesId as $id) {
                $castleId = $id['castleId'];
                $castles[$castleId] = $modelBoard->getCastle($castleId);
                $castle = $castles[$castleId];
                $income += $castle['income'];
                $castleProduction = $modelCastle->getCastleProduction($castleId, $this->playerId);
                if (!$castleProduction['production']) {
                    $unitName = $modelBoard->getMinProductionTimeUnit($castleId);
                    $modelUnit = new Application_Model_Unit();
                    $unitId = $modelUnit->getUnitIdByName($unitName);
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

    private function changeCasteFields($fields, $destX, $destY, $type){
        $fields[$destY][$destX] = $type;
        $fields[$destY + 1][$destX] = $type;
        $fields[$destY][$destX + 1] = $type;
        $fields[$destY + 1][$destX + 1] = $type;
        return $fields;
    }

    private function isCastleFild($armyPosition, $castlePosition){

    }

}

