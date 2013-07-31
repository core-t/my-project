<?php

class Cli_Model_CastleBuildDefense
{

    public function __construct($castleId, $user, $db, $gameHandler)
    {
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
//        $defensePoints = Application_Model_Board::getCastleDefense($castleId);

        $mapCastles = Zend_Registry::get('castles');
        $defensePoints = $mapCastles[$castleId]['defensePoints'];

        $defense = $defenseModifier + $defensePoints;
        if ($defense < 1) {
            $defense = 1;
            $defenseModifier = $defense - $defensePoints;
        }
        $defenseModifier++;

        $costs = 0;
        for ($i = 1; $i <= $defense; $i++) {
            $costs += $i * 100;
        }
        if ($gold < $costs) {
            $gameHandler->sendError($user, 'Za mało złota!');
            return;
        }
        Cli_Model_Database::buildDefense($user->parameters['gameId'], $castleId, $user->parameters['playerId'], $db, $defenseModifier);

        $token = array(
            'type' => 'defense',
            'color' => Cli_Model_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db),
            'gold' => $gold - $costs,
            'defenseMod' => $defenseModifier,
            'castleId' => $castleId
        );

        Cli_Model_Database::updatePlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $token['gold'], $db);

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}