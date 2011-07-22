<?php

class GameloadajaxController extends Warlords_Controller_Action
{

    public function _init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak gameId!');
        }
    }

    public function indexAction()
    {
        // action body
    }

    public function refreshAction()
    {
        // action body
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        $modelGame->updatePlayerInGame($this->_namespace->player['playerId']);
        $response = $modelGame->getPlayersWaitingForGame();
        $this->view->response = Zend_Json::encode($response);
    }


}



