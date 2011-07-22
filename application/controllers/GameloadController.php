<?php

class GameloadController extends Warlords_Controller_Action
{

    public function _init()
    {
        /* Initialize action controller here */
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/gameload.css');
    }

    public function indexAction()
    {
        // action body
        $modelGame = new Application_Model_Game();
        $this->view->myGames = $modelGame->getMyGames($this->_namespace->player['playerId']);
    }

    public function loadAction()
    {
        // action body
        $this->view->headScript()->prependFile($this->view->baseUrl() . '/js/jquery.min.js');
        $this->view->headScript()->appendFile('/js/gameload.js');
        $gameId = $this->_request->getParam('gameId');
        if (!empty($gameId)) {
            $this->_namespace->gameId = $gameId; // zapisuję gemeId do sesji
            $modelGame = new Application_Model_Game($gameId);
            $this->view->colors = $modelGame->getAllColors();
            $modelGame->updatePlayerInGame($this->_namespace->player['playerId']);
            $this->view->game = $modelGame->getGame(); // pobieram informację na temat gry
            $this->_namespace->player['color'] = $modelGame->getPlayerColor($this->_namespace->player['playerId']);
            $this->view->player = $this->_namespace->player;
        } else {
            throw new Exception('Brak gameId!');
        }
    }

}

