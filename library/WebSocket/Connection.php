<?php

interface IWebSocketConnection {

    public function sendHandshakeResponse();

    public function readFrame($data);

    public function sendFrame(IWebSocketFrame $frame);

    public function sendMessage(IWebSocketMessage $msg);

    public function sendString($msg);

    public function getHeaders();

    public function getUriRequested();

    public function getCookies();

    public function getIp();

    public function disconnect();
}

abstract class WebSocket_Connection implements IWebSocketConnection {

    protected $_headers = array();

    /**
     *
     * @var WebSocket_Socket
     */
    protected $_socket = null;
    protected $_cookies = array();
    public $parameters = null;

    public function __construct(WebSocket_Socket $socket, array $headers) {
        $this->setHeaders($headers);
        $this->_socket = $socket;
    }

    public function getIp() {
        return stream_socket_get_name($this->_socket->getResource(), true);
    }

    public function getId() {
        return (int) $this->_socket->getResource();
    }

    public function sendFrame(IWebSocketFrame $frame) {
        if ($this->_socket->write($frame->encode()) === false)
            return FALSE;
    }

    public function sendMessage(IWebSocketMessage $msg) {
        foreach ($msg->getFrames() as $frame) {
            if ($this->sendFrame($frame) === false)
                return FALSE;
        }

        return TRUE;
    }

    public function getHeaders() {
        return $this->_headers;
    }

    public function setHeaders($headers) {
        $this->_headers = $headers;

        if (array_key_exists('Cookie', $this->_headers) && is_array($this->_headers['Cookie'])) {
            $this->cookie = array();
        } else {
            if (array_key_exists("Cookie", $this->_headers)) {
                $this->_cookies = WebSocket_Functions::cookie_parse($this->_headers['Cookie']);
            } else
                $this->_cookies = array();
        }

        $this->getQueryParts();
    }

    public function getCookies() {
        return $this->_cookies;
    }

    public function getUriRequested() {
        if (isset($this->_headers['GET'])) {
            return $this->_headers['GET'];
        }
    }

    protected function getQueryParts() {
        $url = $this->getUriRequested();

        if (($pos = strpos($url, "?")) == -1) {
            $this->parameters = array();
        }

        $q = substr($url, strpos($url, "?") + 1);

        $kvpairs = explode("&", $q);
        $this->parameters = array();

        foreach ($kvpairs as $kv) {
            if (strpos($kv, "=") == -1)
                continue;

            @list($k, $v) = explode("=", $kv);

            $this->parameters[urldecode($k)] = urldecode($v);
        }
    }

    public function getAdminKey() {
        return isset($this->_headers['Admin-Key']) ? $this->_headers['Admin-Key'] : null;
    }

    public function getSocket() {
        return $this->_socket;
    }

}

