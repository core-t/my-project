<?php

class LoadController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/playerslist.css');
        $modelGame = new Application_Model_Game();
        $this->view->myGames = $modelGame->getMyGames($this->_namespace->player['playerId'], $this->_request->getParam('page'));
    }

    public function loadAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $gameId = $this->_request->getParam('gameId');
        if (empty($gameId)) {
            throw new Exception('Brak gameId!');
        }
        $modelGame = new Application_Model_Game($gameId);
        if ($modelGame->playerIsAlive($this->_namespace->player['playerId'])) {
            $this->_namespace->gameId = $gameId; // zapisuję gemeId do sesji
//            $this->_namespace->player['color'] = $modelGame->getPlayerColor($this->_namespace->player['playerId']);
            $this->_redirect('/' . Zend_Registry::get('lang') . '/game');
        } else {
            throw new Exception('Nie powinno Cię tu być!');
        }
    }

}

