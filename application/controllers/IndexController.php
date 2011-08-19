<?php

class IndexController extends Warlords_Controller_Action
{

    public function _init()
    {
        /* Initialize action controller here */
        // przeniosłem sprawdzenie zalogowania do Warlords_Controler_Action
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
        $this->view->headScript()->prependFile($this->view->baseUrl() . '/js/jquery.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jWebSocket.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jwsChannelPlugIn.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/index.websocket.js');
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

    public function unsupportedAction()
    {
        // action body
        
    }
}

