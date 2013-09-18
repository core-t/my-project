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

        $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
        $gold = $mPlayersInGame->getPlayerInGameGold($user->parameters['playerId']) + 1000;

        $mPlayersInGame->updatePlayerInGameGold($user->parameters['playerId'], $gold);
//        $token = Cli_Model_Database::getCastle($user->parameters['gameId'], $castleId, $db);

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'raze',
            'color' => $playersInGameColors[$user->parameters['playerId']],
            'gold' => $gold,
            'castleId' => $castleId
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}