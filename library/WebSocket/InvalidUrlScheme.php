<?php

class WebSocket_InvalidUrlScheme extends Exception {

    public function __construct() {
        parent::__construct("Only 'ws://' urls are supported!");
    }

}

