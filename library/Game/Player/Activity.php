<?php

class Game_Player_Activity {
    const PLAYER_LOGOUT_ACTIVITY_INTERVAL = 600;
    const PLAYER_UPDATE_ACTIVITY_INTERVAL = 500;
    private $_namespace;

    public function __construct () {
        $this->_namespace = Game_Namespace::getNamespace();
    }

    public function logActivity () {
        if ( NULL === $this->_namespace->player ) {
            return false;
        }

        $notActiveInterval = time () - $this->_namespace->player[ 'activity' ];
        if ( $notActiveInterval > Game_Player_Activity::PLAYER_UPDATE_ACTIVITY_INTERVAL ) {

            $singletonPlayer = Application_Model_Singleton_Player::getInstance ();
            $singletonPlayer->getPlayer ();
            $singletonPlayer->updateActivity ();
            $this->_namespace->player[ 'activity' ] = time ();
        }
    }

    public function isActive ( $playerId = null ) {
        $singletonPlayer = Application_Model_Singleton_Player::getInstance ();
        $singletonPlayer->getPlayer ($playerId);
        $notActiveInterval = time() - strtotime( $singletonPlayer->activity );

        if(  $notActiveInterval > Game_Player_Activity::PLAYER_LOGOUT_ACTIVITY_INTERVAL ){
            return false;
        }
        return true;
    }

}
