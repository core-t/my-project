<?php

defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(__DIR__ . '/../application'));
defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));
require_once 'Zend/Application.php';
$application = new Zend_Application(
                APPLICATION_ENV,
                APPLICATION_PATH . '/configs/application.ini'
);
$application->getBootstrap()->bootstrap(array('date', 'config', 'router'));

declare(ticks = 1);

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
class WofSocketServer implements IWebSocketServerObserver {

    protected $debug = true;
    protected $server;

    public function __construct() {
        $this->server = new WebSocket_Server('tcp://' . Zend_Registry::get('config')->websockets->aHost . ':' . Zend_Registry::get('config')->websockets->aPort, 'superdupersecretkey');
        $this->server->addObserver($this);

        $this->server->addUriHandler('wof', new Game_Cli_WofHandler());
    }

    public function onConnect(IWebSocketConnection $user) {
        $this->say("[DEMO] {$user->getId()} connected");
    }

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {
//        $this->say("[DEMO] {$user->getId()} says '{$msg->getData()}'");
    }

    public function onDisconnect(IWebSocketConnection $user) {
        $this->say("[DEMO] {$user->getId()} disconnected");
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

// Start server
$server = new WofSocketServer();
$server->run();

exit;
