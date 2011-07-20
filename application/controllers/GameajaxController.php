<?php

class GameajaxController extends Warlords_Controller_Action {

    public function _init() {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function indexAction() {
        // action body
    }

    public function nextturnAction() {
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        if ($modelGame->isPlayerTurn($this->_namespace->player['playerId'])) {
            $this->view->response = Zend_Json::encode($modelGame->nextTurn($this->_namespace->player['playerId'], $this->_namespace->player['color']));
        }
    }

    public function refreshAction() {
        $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
        $modelArmy = new Application_Model_Army($this->_namespace->gameId);
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        $response = $modelGame->getPlayerTurn();
        $armies = $modelArmy->getPlayerArmies($response['turnPlayerId']);
        $response['armies'] = array();
        foreach ($armies as $army) {
            $response['armies']['army'.$army['armyId']] = $army;
        }
        $response['castles'] = $modelCastle->getPlayerCastles($response['turnPlayerId']);
        $this->view->response = Zend_Json::encode($response);
    }

    public function startmyturnAction() {
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
        $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
        $modelBoard = new Application_Model_Board();
        $castlesId = $modelCastle->getPlayerCastles($this->_namespace->player['playerId']);
        $castles = array();
        foreach($castlesId as $id) {
            $castles[] = $modelBoard->getCastle($id['castleId']);
        }
        $modelArmy->doProduction($this->_namespace->player['playerId'], $castles);
        $armies = $modelArmy->getPlayerArmies($this->_namespace->player['playerId']);
        $array = array();
        foreach ($armies as $k => $army) {
            $array['army'.$army['armyId']] = $army;
            foreach ($army['heroes'] as $hero) {
                if (!isset($moves)) {
                    $moves = $hero['numberOfMoves'];
                }
                if ($hero['numberOfMoves'] < $moves) {
                    $moves = $hero['numberOfMoves'];
                }
            }
            foreach ($army['soldiers'] as $soldier) {
                if (!isset($moves)) {
                    $moves = $soldier['numberOfMoves'];
                }
                if ($soldier['numberOfMoves'] < $moves) {
                    $moves = $soldier['numberOfMoves'];
                }
            }
            $modelArmy->updateArmyPosition($army['armyId'], $this->_namespace->player['playerId'], array('movesLeft' => $moves));
            $array['army'.$army['armyId']]['movesLeft'] = $moves;
        }
        $this->view->response = Zend_Json::encode($array);
    }

    public function addarmyAction() {
        $armyId = $this->_request->getParam('armyId');
        if (!empty($armyId)) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $army = $modelArmy->getArmyById($armyId);
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $army['color'] = $modelGame->getPlayerColor($army['playerId']);
            $this->view->response = Zend_Json::encode($army);
        } else {
            throw new Exception('Brak "armyId"!');
        }
    }
}
