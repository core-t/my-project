<?php

class WebSocket_ConnectionHybi extends WebSocket_Connection {

    private $_openMessage = null;
    private $lastFrame = null;

    public function sendHandshakeResponse() {
        // Check for newer handshake
        $challenge = isset($this->_headers['Sec-Websocket-Key']) ? $this->_headers['Sec-Websocket-Key'] : null;

        // Build response
        $response = "HTTP/1.1 101 WebSocket Protocol Handshake\r\n" . "Upgrade: WebSocket\r\n" . "Connection: Upgrade\r\n";

        // Build HYBI response
        $response .= "Sec-WebSocket-Accept: " . WebSocket_Functions::calcHybiResponse($challenge) . "\r\n\r\n";

        $this->_socket->write($response);

        WebSocket_Functions::say("HYBI Response SENT!");
    }

    public function readFrame($data) {
        $frames = array();
        while (!empty($data)) {
            $frame = WebSocket_Frame::decode($data, $this->lastFrame);

            if ($frame->isReady()) {

                if (WebSocketOpcode::isControlFrame($frame->getType()))
                    $this->processControlFrame($frame);
                else
                    $this->processMessageFrame($frame);

                $this->lastFrame = null;
            } else {
                $this->lastFrame = $frame;
            }

            $frames[] = $frame;
        }

        return $frames;
    }

    /**
     * Process a Message Frame
     *
     * Appends or creates a new message and attaches it to the user sending it.
     *
     * When the last frame of a message is received, the message is sent for processing to the
     * abstract WebSocket::onMessage() method.
     *
     * @param IWebSocketUser $user
     * @param WebSocket_Frame $frame
     */
    protected function processMessageFrame(WebSocket_Frame $frame) {
        if ($this->_openMessage && $this->_openMessage->isFinalised() == false) {
            $this->_openMessage->takeFrame($frame);
        } else {
            $this->_openMessage = WebSocket_Message::fromFrame($frame);
        }

        if ($this->_openMessage && $this->_openMessage->isFinalised()) {
            $this->_socket->onMessage($this->_openMessage);
            $this->_openMessage = null;
        }
    }

    /**
     * Handle incoming control frames
     *
     * Sends Pong on Ping and closes the connection after a Close request.
     *
     * @param IWebSocketUser $user
     * @param WebSocket_Frame $frame
     */
    protected function processControlFrame(WebSocket_Frame $frame) {
        switch ($frame->getType()) {
            case WebSocketOpcode::CloseFrame :
                $frame = WebSocket_Frame::create(WebSocketOpcode::CloseFrame);
                $this->sendFrame($frame);

                $this->_socket->disconnect();
                break;
            case WebSocketOpcode::PingFrame :
                $frame = WebSocket_Frame::create(WebSocketOpcode::PongFrame);
                $this->sendFrame($frame);
                break;
        }
    }

    public function sendString($msg) {
        try {
            $m = WebSocket_Message::create($msg);

            return $this->sendMessage($m);
        } catch (Exception $e) {
            $this->disconnect();
        }
    }

    public function disconnect() {
        $f = WebSocket_Frame::create(WebSocketOpcode::CloseFrame);
        $this->sendFrame($f);

        $this->_socket->disconnect();
    }

}
