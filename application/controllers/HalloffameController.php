<?php

class HalloffameController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $modelGame = new Application_Model_Game();
        $this->view->myGames = $modelGame->getMyGames($this->_namespace->player['playerId'], $this->_request->getParam('page'));
    }

}

