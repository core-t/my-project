<?php

class LoadController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/playerslist.css');
        $modelGame = new Application_Model_Game();
        $this->view->myGames = $modelGame->getMyGames($this->_namespace->player['playerId'], $this->_request->getParam('page'));
    }

}

