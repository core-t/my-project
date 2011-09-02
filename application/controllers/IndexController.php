<?php

class IndexController extends Game_Controller_Action
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
        new Application_View_Helper_Logout($this->view, $this->_namespace->player);
        new Application_View_Helper_Menu($this->view, null);
    }

    public function indexAction()
    {
        // action body
    }

    public function unsupportedAction()
    {
        // action body

    }
}

