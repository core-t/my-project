<?php

class Cli_Model_Surrender
{

    public function __construct($user, $db, $gameHandler)
    {
        $mArmy = new Application_Model_Army($user->parameters['gameId']);
        foreach ($mArmy->getPlayerArmies($user->parameters['playerId']) as $army) {
            $mArmy->destroyArmy($army['armyId'], $user->parameters['playerId']);
        }

        $mCastle = new Application_Model_Castle($this->_namespace->gameId);
        foreach ($mCastle->getPlayerCastles($user->parameters['playerId']) as $castle){
            $mCastle->
        }

        $token = array(
            'type' => 'surrender',
            'playerId' => $user->parameters['playerId']
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}