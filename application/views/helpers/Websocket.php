<?php

class Zend_View_Helper_Websocket extends Zend_View_Helper_Abstract {

    public function Websocket() {

        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/swfobject.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/web_socket.js');

        $url = Zend_Registry::get('config')->websockets->aSchema . '://' . Zend_Registry::get('config')->websockets->aHost . ':' . Zend_Registry::get('config')->websockets->aPort;

        $script = '
        WEB_SOCKET_SWF_LOCATION = "WebSocketMain.swf";
        WEB_SOCKET_DEBUG = true;

        var wsURL = "' . $url . '";
        var wsClosed = true;
        var ws;
        var loading = true;
';
        $this->view->headScript()->appendScript($script);
    }

}

