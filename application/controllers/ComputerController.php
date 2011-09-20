<?php

class ComputerController extends Game_Controller_Action
{
    private $modelGame;
    private $playerId;
    public function _init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
        $this->modelGame = new Application_Model_Game($this->_namespace->gameId);
    }

    public function indexAction()
    {
        // action body
        if(!$this->modelGame->isGameMaster($this->_namespace->player['playerId'])){
            throw new Exception('Nie Twoja gra!');
        }
        $this->playerId = $this->modelGame->getTurnPlayerId();
        $modelPlayer = new Application_Model_Player(null, false);
        if(!$modelPlayer->isComputer($this->playerId)){
            throw new Exception('To nie komputer!');
        }
        if(!$this->modelGame->playerTurnActive($this->playerId)) {
            $this->startTurn();
        }else{
            $this->endTurn();
        }
        
    }
    
    private function endTurn(){
            $youWin = false;
            $response = array();
            $nextPlayer = array(
                'color' => $this->modelGame->getPlayerColor($this->playerId)
            );
            while(empty($response)){
                $nextPlayer = $this->modelGame->nextTurn($nextPlayer['color']);
                $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
                $playerCastlesExists = $modelCastle->playerCastlesExists($nextPlayer['playerId']);
                $modelArmy = new Application_Model_Army($this->_namespace->gameId);
                $playerArmiesExists = $modelArmy->playerArmiesExists($nextPlayer['playerId']);
                if($playerCastlesExists || $playerArmiesExists){
                    $response = $nextPlayer;
                    if($nextPlayer['playerId'] == $this->playerId){
                        $youWin = true;
                        $this->modelGame->endGame();
                    }else{
                        $nr = $this->modelGame->updateTurnNumber($nextPlayer['playerId']);
                        if($nr){
                            $response['nr'] = $nr;
                        }
                        $modelCastle->raiseAllCastlesProductionTurn($this->playerId);
                    }
                    $response['win'] = $youWin;
                }else{
                    $this->modelGame->setPlayerLostGame($nextPlayer['playerId']);
                }
            }
            $response['action'] = 'end';
            $this->view->response = Zend_Json::encode($response);
    }

    private function startTurn(){
        $this->modelGame->turnActivate($this->playerId);
        $modelArmy = new Application_Model_Army($this->_namespace->gameId);
        $modelBoard = new Application_Model_Board();
        $castles = array();
        $modelArmy->resetHeroesMovesLeft($this->playerId);
        $modelArmy->resetSoldiersMovesLeft($this->playerId);
        $gold = $this->modelGame->getPlayerInGameGold($this->playerId);
        $income = 0;
        $costs = 0;
        if($this->modelGame->getTurnNumber() > 0) {
            $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
            $castlesId = $modelCastle->getPlayerCastles($this->playerId);
            foreach($castlesId as $id) {
                $castleId = $id['castleId'];
                $castles[$castleId] = $modelBoard->getCastle($castleId);
                $castle = $castles[$castleId];
                $income += $castle['income'];
                $armyId = $modelArmy->getArmyIdFromPosition($castle['position']);
                if (!$armyId) {
                    $armyId = $modelArmy->createArmy($castle['position'], $this->playerId);
                }
                if (!empty($armyId)) {
                    $castleProduction = $modelCastle->getCastleProduction($castleId, $this->playerId);
                    $castles[$castleId]['productionTurn'] = $castleProduction['productionTurn'];
                    $unitName = $modelBoard->getUnitName($castleProduction['production']);
                    if($castleProduction['production'] AND
                    $castle['production'][$unitName]['time'] <= $castleProduction['productionTurn']
                    AND $castle['production'][$unitName]['cost'] <= $gold
                    ) {
                        if($modelCastle->resetProductionTurn($castleId, $this->playerId) == 1) {
                            $modelArmy->addSoldierToArmy($armyId, $castleProduction['production'], $this->playerId);
                        }
                    }
                }
            }
        }
        $armies = $modelArmy->getPlayerArmies($this->playerId);
        if(empty($castles) && empty($armies)){
            $this->view->response = Zend_Json::encode(array('action' => 'gameover'));
        }else{
            foreach ($armies as $k => $army) {
                foreach($army['soldiers'] as $unit){
                    $costs += $unit['cost'];
                }
            }
            $gold = $gold + $income - $costs;
            $this->modelGame->updatePlayerInGameGold($this->playerId, $gold);

            $this->view->response = Zend_Json::encode(array('action' => 'start'));
        }
        
    }

}

