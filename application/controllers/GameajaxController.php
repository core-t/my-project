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

}
