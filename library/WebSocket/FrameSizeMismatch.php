<?php

class WebSocket_FrameSizeMismatch extends Exception {

    public function __construct(IWebSocketFrame $msg) {
        parent::__construct("Frame size mismatches with the expected frame size. Maybe a buggy client.");
    }

}

