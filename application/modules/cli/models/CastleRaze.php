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
        $position = $mArmy->getArmyPositionByArmyIdPlayerId($armyId, $user->parameters['playerId']);

        $mapCastles = Zend_Registry::get('castles');
        $castleId = Application_Model_Board::isCastleAtPosition($position['x'], $position['y'], $mapCastles);

        $mCastlesInGame = new Application_Model_CastlesInGame($user->parameters['gameId'], $db);

        $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);

        $gold = $mPlayersInGame->getPlayerGold($user->parameters['playerId']);

        if ($mCastlesInGame->razeCastle($castleId, $user->parameters['playerId'])) {
            $defense = $mapCastles[$castleId]['defense'] + $mCastlesInGame->getCastleDefenseModifier($castleId);
            $gold = $gold + $defense * 200;
            $mPlayersInGame->updatePlayerGold($user->parameters['playerId'], $gold);
        }

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