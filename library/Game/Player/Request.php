<?php

class Game_Player_Request {
    const PLAYER_LOGOUT_ACTIVITY_INTERVAL = 600;
    const PLAYER_UPDATE_ACTIVITY_INTERVAL = 500;
    private $_namespace;

    public function __construct () {
        $this->_namespace = Game_Namespace::getNamespace();
    }

    public function logRequest () {
        if ( NULL === $this->_namespace->player ) {
            return false;
        }
        $singletonPlayer = Application_Model_Singleton_Player::getInstance ();
        $singletonPlayer->getPlayer ();
        $singletonPlayer->insertRequest ();
    }

}
