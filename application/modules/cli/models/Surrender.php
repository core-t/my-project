<?php

class Cli_Model_Surrender
{

    public function __construct($user, $db, $gameHandler)
    {
        $mArmy = new Application_Model_Army($user->parameters['gameId'], $db);
        foreach ($mArmy->getPlayerArmies($user->parameters['playerId']) as $army) {
            $mArmy->destroyArmy($army['armyId'], $user->parameters['playerId']);
        }

        $mCastlesInGame = new Application_Model_CastlesInGame($user->parameters['gameId'], $db);
        foreach ($mCastlesInGame->getPlayerCastles($user->parameters['playerId']) as $castle) {
            $mCastlesInGame->razeCastle($castle['castleId'], $user->parameters['playerId']);
        }

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'surrender',
            'color' => $playersInGameColors[$user->parameters['playerId']]
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}