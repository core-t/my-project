<?php

class WebSocket_ConnectionHixie extends WebSocket_Connection {

    private $_clientHandshake;

    public function __construct(WebSocket_Socket $socket, array $headers, $clientHandshake) {
        $this->_clientHandshake = $clientHandshake;
        parent::__construct($socket, $headers);
    }

    public function sendHandshakeResponse() {
        // Last 8 bytes of the client's handshake are used for key calculation later
        $l8b = substr($this->_clientHandshake, -8);

        // Check for 2-key based handshake (Hixie protocol draft)
        $key1 = isset($this->_headers['Sec-Websocket-Key1']) ? $this->_headers['Sec-Websocket-Key1'] : null;
        $key2 = isset($this->_headers['Sec-Websocket-Key2']) ? $this->_headers['Sec-Websocket-Key2'] : null;

        // Origin checking (TODO)
        $origin = isset($this->_headers['Origin']) ? $this->_headers['Origin'] : null;
        $host = $this->_headers['Host'];
        $location = $this->_headers['GET'];

        // Build response
        $response = "HTTP/1.1 101 WebSocket Protocol Handshake\r\n" . "Upgrade: WebSocket\r\n" . "Connection: Upgrade\r\n";

        // Build HIXIE response
        $response .= "Sec-WebSocket-Origin: $origin\r\n" . "Sec-WebSocket-Location: ws://{$host}$location\r\n";
        $response .= "\r\n" . WebSocket_Functions::calcHixieResponse($key1, $key2, $l8b);

        $this->_socket->write($response);
        echo "HIXIE Response SENT!";
    }

    public function readFrame($data) {
        $f = WebSocketFrame76::decode($data);
        $m = WebSocketMessage76::fromFrame($f);

        $this->_socket->onMessage($m);

        return array($f);
    }

    public function sendString($msg) {
        $m = WebSocketMessage76::create($msg);

        return $this->sendMessage($m);
    }

    public function disconnect() {
        $this->_socket->disconnect();
    }

}

