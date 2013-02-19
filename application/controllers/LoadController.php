<?php

class LoadController extends Game_Controller_Gui {

    public function indexAction() {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/playerslist.css');
        $modelGame = new Application_Model_Game();
        $this->view->myGames = $modelGame->getMyGames($this->_namespace->player['playerId']);
    }

    public function loadAction() {
        $gameId = $this->_request->getParam('gameId');
        if (!empty($gameId)) {
            $modelGame = new Application_Model_Game($gameId);
            if ($modelGame->playerIsAlive($this->_namespace->player['playerId'])) {
                $this->_namespace->gameId = $gameId; // zapisuję gemeId do sesji
                $this->_namespace->player['color'] = $modelGame->getPlayerColor($this->_namespace->player['playerId']);
                $this->_redirect('/game');
            } else {
                throw new Exception('Nie powinno Cię tu być!');
            }
        } else {
            throw new Exception('Brak gameId!');
        }
    }

}

