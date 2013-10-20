<?php

class Cli_Model_CastleRaze
{

    public function __construct($armyId, $user, $db, $gameHandler)
    {
        if ($armyId == null) {
            $gameHandler->sendError($user, 'No "armyId"!');
            return;
        }

        $mArmy = new Application_Model_Army($user->parameters['gameId'], $db);
        $mArmy->g

        Application_Model_Board::isCastleAtPosition();

        $mCastlesInGame = new Application_Model_CastlesInGame($user->parameters['gameId'], $db);
        $mCastlesInGame->razeCastle($castleId, $user->parameters['playerId']);

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