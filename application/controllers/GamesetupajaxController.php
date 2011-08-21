<?php

class GamesetupajaxController extends Game_Controller_Action {

    public function _init() {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
    }

    public function indexAction() {
        // action body
    }

    public function refreshAction() {
        if (!empty($this->_namespace->gameId)) {
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $modelGame->updatePlayerInGame($this->_namespace->player['playerId']);
            $response = $modelGame->getPlayersWaitingForGame();
            $gamestart = $modelGame->isGameStarted();
            $response['start'] = $gamestart;
            $this->view->response = Zend_Json::encode($response);
        } else {
            throw new Exception('Brak gameId!');
        }
    }

    public function gamestartAction() {
        if (!empty($this->_namespace->gameId)) {
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            if ($modelGame->isGameMaster($this->_namespace->player['playerId'])) {
                $modelGame->startGame();
            }
        } else {
            throw new Exception('Brak gameId!');
        }
    }

    public function readyAction() {
        $color = $this->_request->getParam('color');
        if (!empty($this->_namespace->gameId) OR !empty($color)) {
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $res = $modelGame->updatePlayerReady($this->_namespace->player['playerId'], $color);
            $this->view->response = Zend_Json::encode($res);
//             echo $res;
        } else {
            throw new Exception('Brak gameId!');
        }
    }

    public function allarmiesreadyAction() {
        $modelArmy = new Application_Model_Army($this->_namespace->gameId);
        if ($modelArmy->allArmiesReady()) {
            $this->view->response = Zend_Json::encode(array('all' => true));
        }
    }

}

