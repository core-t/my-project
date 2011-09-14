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

    public function humanaiAction(){
        $color = $this->_request->getParam('color');
        if (!empty($color)) {
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            if ($modelGame->isGameMaster($this->_namespace->player['playerId'])) {
                $modelPlayer = new Application_Model_Player(null, false);
                $data = array(
                    'firstName' => 'Computer',
                    'lastName' => 'Player',
                    'computer' => 'true'
                );
                $playerId = $modelPlayer->createPlayer($data);
                $modelHero = new Application_Model_Hero($playerId);
                $modelHero->createHero();
                $modelGame->joinGame($playerId);
                $modelGame->updatePlayerReady($playerId, $color);
            }
        } else {
            throw new Exception('Brak color!');
        }
    }

    public function gamestartAction() {
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        if ($modelGame->isGameMaster($this->_namespace->player['playerId'])) {
            $modelGame->startGame();
            $computerPlayers = $modelGame->getComputerPlayers();
            foreach($computerPlayers as $computer){
                $this->startComputerPlayer($computer['playerId'], $computer['color']);
            }
//            $modelGame->setFirstTurnPlayerId();
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
            $result = array('all' => true);
        } else {
            $result = array('all' => false);
        }
        $this->view->response = Zend_Json::encode($result);
    }

    private function startComputerPlayer($playerId, $color){
        $modelArmy = new Application_Model_Army($this->_namespace->gameId);
        $modelBoard = new Application_Model_Board();
        $modelHero = new Application_Model_Hero($playerId);
        $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
        $startPositions = $modelBoard->getDefaultStartPositions();
        $playerHeroes = $modelHero->getHeroes();
        if(empty($playerHeroes)) {
            $modelHero->createHero();
            $playerHeroes = $modelHero->getHeroes();
        }
        $armyId = $modelArmy->createArmy(
                $startPositions[$color]['position'],
                $playerId);
        $res = $modelArmy->addHeroToGame($armyId, $playerHeroes[0]['heroId']);
        $modelCastle->addCastle($startPositions[$color]['id'], $playerId);
    }

}

