<?php

class Cli_Model_CastleBuildDefense
{

    public function __construct($castleId, $user, $db, $gameHandler)
    {
        if ($castleId == null) {
            $gameHandler->sendError($user, 'Brak "castleId"!');
            return;
        }

        $mCastlesInGame = new Application_Model_CastlesInGame($user->parameters['gameId'], $db);
        if (!$mCastlesInGame->isPlayerCastle($castleId, $user->parameters['playerId'])) {
            $gameHandler->sendError($user, 'To nie jest Twój zamek.');
            return;
        }

        $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
        $gold = $mPlayersInGame->getPlayerInGameGold($user->parameters['playerId']);

        $defenseModifier = $mCastlesInGame->getCastleDefenseModifier($castleId);

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

        $mCastlesInGame = new Application_Model_CastlesInGame($user->parameters['gameId'], $db);
        $mCastlesInGame->buildDefense($castleId, $user->parameters['playerId'], $defenseModifier);

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'defense',
            'color' => $playersInGameColors[$user->parameters['playerId']],
            'gold' => $gold - $costs,
            'defenseMod' => $defenseModifier,
            'castleId' => $castleId
        );

        $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
        $mPlayersInGame->updatePlayerInGameGold($user->parameters['playerId'], $token['gold']);

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}