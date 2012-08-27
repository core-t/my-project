<?php

class Application_Model_WebSocket {

    protected $_client;
    protected $_utid = 2;

    public function __construct() {
        $url = Zend_Registry::get('config')->websockets->aSchema . '://' . Zend_Registry::get('config')->websockets->aHost . ':' . Zend_Registry::get('config')->websockets->aPort;
        $this->_client = new WebSocket_WebSocket($url);
        $this->_client->open();

        $msg = WebSocket_Message::create('{"ns":"org.jwebsocket.plugins.system","type":"login","username":"root","password":"36e69ae581319d174976f8980ee7e7ff","encoding":null,"pool":null,"utid":' . $this->_utid . '}');
        $this->_client->sendMessage($msg);

        $msg1 = $this->_client->readMessage();
        $data1 = $msg1->getData();
        if (!strpos($data1, '"type":"welcome"')) {
            throw new Exception('Nie działa WebSocket');
        }
        $msg2 = $this->_client->readMessage();
        $data2 = $msg2->getData();
        if (!strpos($data2, ',"code":0,"msg":"ok"')) {
            $res = explode(',', $data2);
            $msg = explode(':', $res[2]);
            throw new Exception('WebSocket - ' . $msg[1]);
        }
        $this->utidIncrement();
    }

    private function utidIncrement() {
        $this->_utid++;
    }

    public function createChannel($game) {
        $msg = WebSocket_Message::create('{"ns":"org.jwebsocket.plugins.channels","type":"createChannel","channel":"ch' . $game['gameId'] . '","name":"ch' . $game['gameId'] . '","isPrivate":true,"isSystem":false,"accessKey":"' . $game['lAccessKey'] . '","secretKey":"' . $game['lSecretKey'] . '","owner":"root","password":null,"utid":' . $this->_utid . '}');
        $this->_client->sendMessage($msg);
        $msg = $this->_client->readMessage();
        $data = $msg->getData();
        if (!strpos($data, '"code":0,"msg":"ok"')) {
            $res = explode(',', $data);
            $ws_msg = explode(':', $res[2]);
            if (!strpos($ws_msg[1], 'already exists')) {
                throw new Exception('WebSocket - ' . $ws_msg[1]);
            }
        }
        $this->utidIncrement();
    }

    public function authorizeChannel($game) {
        $msg = WebSocket_Message::create('{"ns":"org.jwebsocket.plugins.channels","type":"authorize","channel":"ch' . $game['gameId'] . '","accessKey":"' . $game['lAccessKey'] . '","secretKey":"' . $game['lSecretKey'] . '","utid":' . $this->_utid . '}');
        $this->_client->sendMessage($msg);
        $msg = $this->_client->readMessage();
        $data = $msg->getData();
        if (!strpos($data, '"code":0,"msg":"ok"')) {
            $res = explode(',', $data);
            $ws_msg = explode(':', $res[2]);
            throw new Exception('WebSocket - ' . $ws_msg[1]);
        }
        $this->utidIncrement();
    }

    public function publishChannel($gameId, $data) {
        $msg = WebSocket_Message::create('{"ns":"org.jwebsocket.plugins.channels","type":"publish","channel":"ch' . $gameId . '","data":"' . $data . '","utid":' . $this->_utid . '}');
        $this->_client->sendMessage($msg);
        $msg = $this->_client->readMessage();
        $data = $msg->getData();
        if (!strpos($data, '"code":0,"msg":"ok"')) {
            $res = explode(',', $data);
            $ws_msg = explode(':', $res[2]);
            throw new Exception('WebSocket - ' . $ws_msg[1]);
        }
        $this->utidIncrement();
    }

    public function close() {
        $this->_client->close();
    }

}

