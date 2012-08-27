<?php

class WebSocket_NotAuthorizedException extends Exception {

    protected $user;

    public function __construct(IWebSocketUser $user) {
        parent::__construct("None or invalid credentials provided!");
        $this->user = $user;
    }

}

