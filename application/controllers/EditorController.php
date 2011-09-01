<?php

class EditorController extends Game_Controller_Action
{

    public function _init()
    {
        /* Initialize action controller here */
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
        $this->view->headScript()->prependFile($this->view->baseUrl() . '/js/jquery.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jWebSocket.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jwsChannelPlugIn.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/index.websocket.js');
    }

    public function indexAction()
    {
        // action body
    }


}

