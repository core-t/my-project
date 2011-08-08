<?php

class TurnController extends Warlords_Controller_Action {

    public function _init() {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function nextAction() {
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        if ($modelGame->isPlayerTurn($this->_namespace->player['playerId'])) {
            $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
            $modelCastle->raiseAllCastlesProductionTurn($this->_namespace->player['playerId']);
            $this->view->response = Zend_Json::encode($modelGame->nextTurn($this->_namespace->player['playerId'], $this->_namespace->player['color']));
        }
    }

    public function startAction() {
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        if(!$modelGame->isPlayerTurn($this->_namespace->player['playerId'])) {
            throw new Exception('To nie jest moja tura.');
            return false;
        }
        if($modelGame->isPlayerTurnActive($this->_namespace->player['playerId'])) {
            throw new Exception('Tura już aktywna. Próba ponownoego aktywowania tury i wygenerowania produkcji.');
            return false;
        }
        $modelGame->turnActivate($this->_namespace->player['playerId']);
        $modelArmy = new Application_Model_Army($this->_namespace->gameId);
        $modelBoard = new Application_Model_Board();
        $castles = array();
        $modelArmy->resetHeroesMovesLeft($this->_namespace->player['playerId']);
        $modelArmy->resetSoldiersMovesLeft($this->_namespace->player['playerId']);
        $gold = $modelGame->getPlayerInGameGold($this->_namespace->player['playerId']);
        $income = 0;
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
                    if($castleProduction['production'] AND $castle['production'][$modelBoard->getUnitName($castleProduction['production'])]['time'] <= $castleProduction['productionTurn']) {
                        if($modelCastle->resetProductionTurn($castleId, $this->_namespace->player['playerId']) == 1) {
                            $modelArmy->addSoldierToArmy($armyId, $castleProduction['production'], $this->_namespace->player['playerId']);
                        }
                    }
                }
            }
        }
        $armies = $modelArmy->getPlayerArmies($this->_namespace->player['playerId']);
        if(empty($castles) && empty($armies)){
            $modelGame->setPlayerLostGame($this->_namespace->player['playerId']);
            $this->view->response = Zend_Json::encode(array('gameover'=>1));
        }else{
            $array = array();
            $resutl = array();
            $costs = 0;
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
            $resutl['gameover'] = 0;
            $this->view->response = Zend_Json::encode($resutl);
        }
    }
}
