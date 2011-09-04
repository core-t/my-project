<?php

class TowerController extends Game_Controller_Action {

    public function _init() {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function addAction(){
        $towerId = $this->_request->getParam('tid');
        if ($towerId !== null) {
            $modelTower = new Application_Model_Tower($this->_namespace->gameId);
            if($modelTower->towerExists($towerId)){
                Zend_Debug::dump('dupa');
                $modelTower->changeTowerOwner($towerId, $this->_namespace->player['playerId']);
            }else{
                $modelTower->addTower($towerId, $this->_namespace->player['playerId']);
            }
        } else {
            throw new Exception('Brak "towerId"!');
        }
    }
    
    public function getAction(){
        $towerId = $this->_request->getParam('tid');
        if ($towerId !== null) {
            $modelTower = new Application_Model_Tower($this->_namespace->gameId);
            if($modelTower->towerExists($towerId)){
                $this->view->response = Zend_Json::encode($modelTower->getTower($towerId));
            }else{
                throw new Exception('Nie istnieje!');
            }
        } else {
            throw new Exception('Brak "towerId"!');
        }
    }
}
