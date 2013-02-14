<?php

class NewController extends Game_Controller_Gui {

    public function indexAction() {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/playerslist.css?v=' . Zend_Registry::get('config')->version);
        if ($this->_namespace->gameId) {
            unset($this->_namespace->gameId);
        }
        $modelGame = new Application_Model_Game();
        $this->view->openGames = $modelGame->getOpen();
        $this->view->player = $this->_namespace->player;
    }

    public function createAction() {
        $this->view->form = new Application_Form_Creategame ();
        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                $modelGame = new Application_Model_Game ();
                $gameId = $modelGame->createGame($this->_request->getParam('numberOfPlayers'), $this->_namespace->player['playerId']);
                if ($gameId) {
                    $colors = $modelGame->getAllColors();
                    $modelGame->joinGame($this->_namespace->player['playerId']);
                    $modelGame->updatePlayerReady($this->_namespace->player['playerId'], $colors[0]);
                    for ($i = 1; $i < $this->_request->getParam('numberOfPlayers'); $i++)
                    {
                        $playerId = $modelGame->getComputerPlayerId();
                        if (!$playerId) {
                            $modelPlayer = new Application_Model_Player(null, false);
                            $playerId = $modelPlayer->createComputerPlayer();
                            $modelHero = new Application_Model_Hero($playerId);
                            $modelHero->createHero();
                        }
                        $modelGame->joinGame($playerId);
                        $modelGame->updatePlayerReady($playerId, $colors[$i]);
                    }
                    $this->_helper->redirector('index', 'gamesetup', null, array('gameId' => $gameId));
                }
            }
        }
    }

}

