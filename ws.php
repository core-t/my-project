<?php
require_once dirname(__FILE__).'/library/WS/server.php';

class WarlordsUser extends WSBaseUser {
    protected $maxIdle = 30;
}

class WarlordsConfig extends WSBaseConfig {
    public $address = 'localhost';
    public $post = 12345;
    public $userClass = 'WarlordsUser';
}

class WarlordsServer extends WebSocketServer {

    protected $configClass = 'WarlordsConfig';

    function process($user, $msg){
        switch($msg->event) {
            case 'ping':
                break;
            case 'move':
                $this->sentToAllBut($user, json_encode($msg));
                break;
            case 'turn':
                $this->sentToAllBut($user, json_encode($msg));
                break;
            case 'add':
                $this->sentToAllBut($user, json_encode($msg));
                break;
            case 'delete':
                $this->sentToAllBut($user, json_encode($msg));
                break;
            case 'castleOwner':
                var_dump(json_encode($msg));
                $this->sentToAllBut($user, json_encode($msg));
                break;
            default:
                var_dump(json_encode($msg));
                break;
        }
    }
}

$server = new WarlordsServer();
$server->run();
?>
