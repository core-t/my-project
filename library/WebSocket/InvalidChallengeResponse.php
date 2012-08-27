<?php

class WebSocket_InvalidChallengeResponse extends Exception {

    public function __construct() {
        parent::__construct("Server send an incorrect response to the clients challenge!");
    }

}

