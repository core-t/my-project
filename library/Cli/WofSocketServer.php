<?php

interface IWebSocketServerObserver {

    public function onConnect(IWebSocketConnection $user);

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg);

    public function onDisconnect(IWebSocketConnection $user);

    public function onAdminMessage(IWebSocketConnection $user, IWebSocketMessage $msg);
}

/**
 * WOF socket server.
 *
 *
 * @author Bartosz Krzeszewski
 *
 */
class Cli_WofSocketServer implements IWebSocketServerObserver {

    protected $debug = true;
    protected $server;

    public function __construct() {
        $this->server = new WebSocket_Server('tcp://' . Zend_Registry::get('config')->websockets->aHost . ':' . Zend_Registry::get('config')->websockets->aPort, 'superdupersecretkey');
        $this->server->addObserver($this);

        $this->server->addUriHandler('game', new Cli_Model_GameHandler());
        $this->server->addUriHandler('public', new Cli_Model_PublicHandler());
    }

    public function onConnect(IWebSocketConnection $user) {
//        $this->say("[DEMO] {$user->getId()} connected");
    }

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {
//        $this->say("[DEMO] {$user->getId()} says '{$msg->getData()}'");
    }

    public function onDisconnect(IWebSocketConnection $user) {
//        $this->say("[DEMO] {$user->getId()} disconnected");
    }

    public function onAdminMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {
        $this->say("[DEMO] Admin Message received!");

        $frame = WebSocketFrame::create(WebSocketOpcode::PongFrame);
        $user->sendFrame($frame);
    }

    public function say($msg) {
        echo "$msg \r\n";
    }

    public function run() {
        $this->server->run();
    }

}
