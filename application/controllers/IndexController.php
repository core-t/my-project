<?php

class IndexController extends Game_Controller_Action {

    public function _init() {
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
        $this->view->headScript()->prependFile($this->view->baseUrl() . '/js/jquery.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jWebSocket.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jwsChannelPlugIn.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/index.websocket.js');
        new Application_View_Helper_Logout($this->_namespace->player);
    }

    public function indexAction() {
        new Application_View_Helper_Menu();
        new Application_View_Helper_Websocket();
    }

    public function unsupportedAction() {
        // action body
    }

}
