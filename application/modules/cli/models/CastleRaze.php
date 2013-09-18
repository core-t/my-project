<?php

class Cli_Model_CastleRaze
{

    public function __construct($castleId, $user, $db, $gameHandler)
    {
        if ($castleId == null) {
            $gameHandler->sendError($user, 'No "castleId"!');
            return;
        }

        Cli_Model_Database::razeCastle($user->parameters['gameId'], $castleId, $user->parameters['playerId'], $db);
        $gold = Cli_Model_Database::getPlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $db) + 1000;
        Cli_Model_Database::updatePlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $gold, $db);
//        $token = Cli_Model_Database::getCastle($user->parameters['gameId'], $castleId, $db);

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'raze',
            'color' => $playersInGameColors($user->parameters['playerId']),
            'gold' => $gold,
            'castleId' => $castleId
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}