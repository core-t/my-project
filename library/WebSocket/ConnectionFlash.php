<?php

class WebSocket_ConnectionFlash {

    public function __construct($socket, $data) {
        $this->_socket = $socket;
        $this->_socket->onFlashXMLRequest($this);
    }

    public function sendString($msg) {
        $this->_socket->write($msg);
    }

    public function disconnect() {
        $this->_socket->disconnect();
    }

}