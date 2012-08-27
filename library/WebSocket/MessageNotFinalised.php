<?php

class WebSocket_MessageNotFinalised extends Exception {

    public function __construct(IWebSocketMessage $msg) {
        parent::__construct("WebSocketMessage is not finalised!");
    }

}

