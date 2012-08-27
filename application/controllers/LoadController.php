<?php

class LoadController extends Game_Controller_Action {

    public function _init() {
        /* Initialize action controller here */
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/playerslist.css');
        $this->view->headScript()->prependFile($this->view->baseUrl() . '/js/jquery.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jWebSocket.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jwsChannelPlugIn.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/index.websocket.js');
        new Application_View_Helper_Logout($this->_namespace->player);
        new Application_View_Helper_Menu();
        new Application_View_Helper_Websocket();
    }

    public function indexAction() {
        // action body
        $modelGame = new Application_Model_Game();
        $this->view->myGames = $modelGame->getMyGames($this->_namespace->player['playerId']);
    }

    public function loadAction() {
        // action body
        $this->view->headScript()->appendFile('/js/load/load.js');
        $gameId = $this->_request->getParam('gameId');
        if (!empty($gameId)) {
            $modelGame = new Application_Model_Game($gameId);
            if ($modelGame->playerIsAlive($this->_namespace->player['playerId'])) {
                $this->_namespace->gameId = $gameId; // zapisuję gemeId do sesji
                $this->view->colors = $modelGame->getAllColors();
                $this->view->game = $modelGame->getGame(); // pobieram informację na temat gry
                $this->view->game['alive'] = $modelGame->getAlivePlayers();
                $this->_namespace->player['color'] = $modelGame->getPlayerColor($this->_namespace->player['playerId']);
                $this->view->player = $this->_namespace->player;
            } else {
                throw new Exception('Nie powinno Cię tu być!');
            }
        } else {
            throw new Exception('Brak gameId!');
        }
    }

}

