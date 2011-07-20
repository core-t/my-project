<?php

class IndexController extends Warlords_Controller_Action
{

    public function _init()
    {
        /* Initialize action controller here */
        // przeniosłem sprawdzenie zalogowania do Warlords_Controler_Action
    }

    public function indexAction()
    {
        // action body
        $modelPlayer = new Application_Model_Player($this->_namespace->fbId);
        $this->_namespace->player = $modelPlayer->getPlayer();
        if(empty($this->_namespace->player['playerId'])) {
            throw new Exception('Brak playerId');
        }
    }


}

