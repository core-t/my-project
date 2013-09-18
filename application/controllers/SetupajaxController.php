<?php

class SetupajaxController extends Game_Controller_Action
{

    public function _init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function allarmiesreadyAction()
    {
        $mArmy = new Application_Model_Army($this->_namespace->gameId);
        $numberOfArmies = $mArmy->getNumberOfArmies();

        $mPlayersInGame = new Application_Model_PlayersInGame($this->_namespace->gameId);
        $numberOfPlayers = $mPlayersInGame->getNumberOfPlayers();

        if ($numberOfArmies >= $numberOfPlayers) {
            $result = array('all' => true);
        } else {
            $result = array('all' => false);
        }
        echo Zend_Json::encode($result);
    }

}

