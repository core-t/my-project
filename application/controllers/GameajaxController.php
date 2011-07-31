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
}
