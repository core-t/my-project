<?php

class Cli_Model_CastleRaze {

    public function __construct($castleId, $user, $db, $gameHandler) {
        if ($castleId == null) {
            $gameHandler->sendError($user, 'Brak "castleId"!');
            return;
        }

        Cli_Model_Database::razeCastle($user->parameters['gameId'], $castleId, $user->parameters['playerId'], $db);
        $gold = Cli_Model_Database::getPlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $db) + 1000;
        Cli_Model_Database::updatePlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $gold, $db);
        $token = Cli_Model_Database::getCastle($user->parameters['gameId'], $castleId, $db);
        $token['color'] = Cli_Model_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db);
        $token['gold'] = $gold;
        $token['type'] = 'castle';

        $gameHandler->sendToChannel($token, Cli_Model_Database::getInGameWSSUIds($user->parameters['gameId'], $db));
    }

}