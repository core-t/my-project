<?php

class GameajaxController extends Warlords_Controller_Action {

    public function _init() {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
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

    public function armiesAction(){
        $color = $this->_request->getParam('color');
        if (!empty($color)) {
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $playerId = $modelGame->getPlayerIdByColor($color);
            if(!empty($playerId)){
                $modelArmy = new Application_Model_Army($this->_namespace->gameId);
                $this->view->response = Zend_Json::encode($modelArmy->getPlayerArmies($playerId));
            }else{
                throw new Exception('Brak $playerId!');
            }
        } else {
            throw new Exception('Brak "color"!');
        }
    }

    public function splitAction(){
        $armyId = $this->_request->getParam('aid');
        $h = $this->_request->getParam('h');
        $s = $this->_request->getParam('s');
        if (!empty($armyId) && (!empty($h) || !empty($s))) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $armyId = $modelArmy->splitArmy($h, $s, $armyId, $this->_namespace->player['playerId']);
            $this->view->response = Zend_Json::encode($modelArmy->getArmyById($armyId));
        } else {
            throw new Exception('Brak "armyId", "s"!');
        }
    }

    public function disbandAction(){
        $armyId = $this->_request->getParam('aid');
        if (!empty($armyId)) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $response = $modelArmy->destroyArmy($armyId, $this->_namespace->player['playerId']);
            $this->view->response = Zend_Json::encode($response);
        } else {
            throw new Exception('Brak "armyId"!');
        }
    }

    public function ruinsAction(){
        $armyId = $this->_request->getParam('aid');
        if (!empty($armyId)) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $heroId = $modelArmy->getHeroIdByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
            if(empty($heroId)){
                throw new Exception('Brak heroId. Tylko Hero może przeszukiwać ruiny!');
            }
            $position = $modelArmy->getArmyPositionByArmyId($armyId, $this->_namespace->player['playerId']);
            $position = explode(',', substr($position['position'], 1 , -1));
            $ruinId = Application_Model_Board::confirmRuinPosition($position);
            if($ruinId !== null){
                $modelRuin = new Application_Model_Ruin($this->_namespace->gameId);
                if($modelRuin->ruinExists($ruinId)){
                    throw new Exception('Ruiny są już przeszukane. '.$ruinId.' '.$armyId);
                }
                $modelRuin->addRuin($ruinId);
                $unitId = rand(11,15);
                $numerOfUnits = rand(1,3);
                for($i = 0; $i < $numerOfUnits; $i++){
                    $modelArmy->addSoldierToArmy($armyId, $unitId, $this->_namespace->player['playerId']);
                }
                $modelArmy->zeroHeroMovesLeft($armyId, $heroId, $this->_namespace->player['playerId']);
                $this->view->response = Zend_Json::encode($modelArmy->getArmyById($armyId));
            }else{
                throw new Exception('Brak ruinId');
            }
        } else {
            throw new Exception('Brak "armyId"!');
        }
    }
}
