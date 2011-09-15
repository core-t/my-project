<?php

class TurnController extends Game_Controller_Action {

    public function _init() {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function nextAction() {
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        if ($modelGame->playerLost($this->_namespace->player['playerId'])) {
            return null;
        }
        if ($modelGame->isPlayerTurn($this->_namespace->player['playerId'])) {
            $youWin = false;
            $response = array();
            $nextPlayer = array(
                'color' => $this->_namespace->player['color']
            );
            while(empty($response)){
                $nextPlayer = $modelGame->nextTurn($nextPlayer['color']);
                $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
                $playerCastlesExists = $modelCastle->playerCastlesExists($nextPlayer['playerId']);
                $modelArmy = new Application_Model_Army($this->_namespace->gameId);
                $playerArmiesExists = $modelArmy->playerArmiesExists($nextPlayer['playerId']);
                if($playerCastlesExists || $playerArmiesExists){
                    $response = $nextPlayer;
                    if($nextPlayer['playerId'] == $this->_namespace->player['playerId']){
                        $youWin = true;
                        $modelGame->endGame();
                    }else{
                        $nr = $modelGame->updateTurnNumber($nextPlayer['playerId']);
                        if($nr){
                            $response['nr'] = $nr;
                        }
                        $modelCastle->raiseAllCastlesProductionTurn($this->_namespace->player['playerId']);
                    }
                    $response['win'] = $youWin;
                }else{
                    $modelGame->setPlayerLostGame($nextPlayer['playerId']);
                }
            }
            $this->view->response = Zend_Json::encode($response);
        }
    }

    public function getAction() {
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        if ($modelGame->playerLost($this->_namespace->player['playerId'])) {
            $this->view->response = Zend_Json::encode(array('lost' => 1));
            return null;
        }
        $this->view->response = Zend_Json::encode($modelGame->getTurn());
    }

    public function startAction() {
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        if(!$modelGame->isPlayerTurn($this->_namespace->player['playerId'])) {
            throw new Exception('To nie jest moja tura.');
            return false;
        }
        if($modelGame->playerTurnActive($this->_namespace->player['playerId'])) {
            throw new Exception('Tura jest już aktywna. Próba ponownego aktywowania tury i wygenerowania produkcji.');
        }
        $modelGame->turnActivate($this->_namespace->player['playerId']);
        $modelArmy = new Application_Model_Army($this->_namespace->gameId);
        $modelBoard = new Application_Model_Board();
        $castles = array();
        $modelArmy->resetHeroesMovesLeft($this->_namespace->player['playerId']);
        $modelArmy->resetSoldiersMovesLeft($this->_namespace->player['playerId']);
        $gold = $modelGame->getPlayerInGameGold($this->_namespace->player['playerId']);
        $income = 0;
        $costs = 0;
        if($modelGame->getTurnNumber() > 0) {
            $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
            $castlesId = $modelCastle->getPlayerCastles($this->_namespace->player['playerId']);
            foreach($castlesId as $id) {
                $castleId = $id['castleId'];
                $castles[$castleId] = $modelBoard->getCastle($castleId);
                $castle = $castles[$castleId];
                $income += $castle['income'];
                $armyId = $modelArmy->getArmyIdFromPosition($castle['position']);
                if (!$armyId) {
                    $armyId = $modelArmy->createArmy($castle['position'], $this->_namespace->player['playerId']);
                }
                if (!empty($armyId)) {
                    $castleProduction = $modelCastle->getCastleProduction($castleId, $this->_namespace->player['playerId']);
                    $castles[$castleId]['productionTurn'] = $castleProduction['productionTurn'];
                    $unitName = $modelBoard->getUnitName($castleProduction['production']);
                    if($castleProduction['production'] AND
                    $castle['production'][$unitName]['time'] <= $castleProduction['productionTurn']
                    AND $castle['production'][$unitName]['cost'] <= $gold
                    ) {
                        if($modelCastle->resetProductionTurn($castleId, $this->_namespace->player['playerId']) == 1) {
                            $modelArmy->addSoldierToArmy($armyId, $castleProduction['production'], $this->_namespace->player['playerId']);
                        }
                    }
                }
            }
        }
        $armies = $modelArmy->getPlayerArmies($this->_namespace->player['playerId']);
        if(empty($castles) && empty($armies)){
            $this->view->response = Zend_Json::encode(array('gameover'=>1));
        }else{
            $array = array();
            $resutl = array();
            foreach ($armies as $k => $army) {
                foreach($army['soldiers'] as $unit){
                    $costs += $unit['cost'];
                }
                $array['army'.$army['armyId']] = $army;
            }
            $gold = $gold + $income - $costs;
            $modelGame->updatePlayerInGameGold($this->_namespace->player['playerId'], $gold);
            $resutl['gold'] = $gold;
            $resutl['costs'] = $costs;
            $resutl['income'] = $income;
            $resutl['armies'] = $array;
            $resutl['castles'] = $castles;
            $resutl['gameover'] = 0;
            $this->view->response = Zend_Json::encode($resutl);
        }
    }
}
