<?php

class GamesetupajaxController extends Game_Controller_Action {

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

    public function refreshAction() {
        $kick = false;
        $gamestart = false;
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        $modelGame->updateGameMaster($this->_namespace->player['playerId']);
        $res = $modelGame->updatePlayerInGame($this->_namespace->player['playerId']);
        if($res == 1){
            $response = $modelGame->getPlayersWaitingForGame();
            $gamestart = $modelGame->isGameStarted();
        }else{
            $kick = true;
        }
        $response['start'] = $gamestart;
        $response['kick'] = $kick;
        $this->view->response = Zend_Json::encode($response);
    }

    public function kickAction(){
        $color = $this->_request->getParam('color');
        if (!empty($color)) {
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $modelGame->kick($color, $this->_namespace->player['playerId']);
        } else {
            throw new Exception('Brak color!');
        }
    }

    public function gamestartAction() {
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        if ($modelGame->isGameMaster($this->_namespace->player['playerId'])) {
            $modelGame->startGame();
        }
    }

    public function readyAction() {
        $color = $this->_request->getParam('color');
        if (!empty($color)) {
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $res = $modelGame->updatePlayerReady($this->_namespace->player['playerId'], $color);
            $this->view->response = Zend_Json::encode($res);
//             echo $res;
        } else {
            throw new Exception('Brak color!');
        }
    }

    public function allarmiesreadyAction() {
        $modelArmy = new Application_Model_Army($this->_namespace->gameId);
        if ($modelArmy->allArmiesReady()) {
            $this->view->response = Zend_Json::encode(array('all' => true));
        }
    }

}

