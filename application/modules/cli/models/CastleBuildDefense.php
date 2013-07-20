<?php

class Cli_Model_CastleBuildDefense {

    public function __construct($castleId, $user, $db, $gameHandler) {
        if ($castleId == null) {
            $gameHandler->sendError($user, 'Brak "castleId"!');
            return;
        }

        if (!Cli_Model_Database::isPlayerCastle($user->parameters['gameId'], $castleId, $user->parameters['playerId'], $db)) {
            $gameHandler->sendError($user, 'To nie jest Twój zamek.');
            return;
        }
        $gold = Cli_Model_Database::getPlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $db);
        $defenseModifier = Cli_Model_Database::getCastleDefenseModifier($user->parameters['gameId'], $castleId, $db);
        $defensePoints = Application_Model_Board::getCastleDefense($castleId);
        $defense = $defenseModifier + $defensePoints;
        $costs = 0;
        for ($i = 1; $i <= $defense; $i++)
        {
            $costs += $i * 100;
        }
        if ($gold < $costs) {
            $gameHandler->sendError($user, 'Za mało złota!');
            return;
        }
        Cli_Model_Database::buildDefense($user->parameters['gameId'], $castleId, $user->parameters['playerId'], $db);
        $token = Cli_Model_Database::getCastle($user->parameters['gameId'], $castleId, $db);
        $token['defensePoints'] = $defensePoints;
        $token['color'] = Cli_Model_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db);
        $token['gold'] = $gold - $costs;
        $token['type'] = 'castle';
        Cli_Model_Database::updatePlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $token['gold'], $db);

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}