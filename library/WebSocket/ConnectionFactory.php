<?php

class WebSocket_ConnectionFactory {

    public static function fromSocketData(WebSocket_Socket $socket, $data) {
        $headers = WebSocket_Functions::parseHeaders($data);

        if (isset($headers['Sec-Websocket-Key1'])) {
            $s = new WebSocket_ConnectionHixie($socket, $headers, $data);
            $s->sendHandshakeResponse();
        } else if (strpos($data, '<policy-file-request/>') === 0) {
            $s = new WebSocket_ConnectionFlash($socket, $data);
        } else {
            $s = new WebSocket_ConnectionHybi($socket, $headers);
            $s->sendHandshakeResponse();
        }

        return $s;
    }

}
