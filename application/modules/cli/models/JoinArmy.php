<?php

class Cli_Model_JoinArmy
{

    public function __construct($armyId, $user, $db, $gameHandler)
    {
        if (empty($armyId)) {
            $gameHandler->sendError($user, 'Brak "armyId"!');
            return;
        }

        $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);
        $position = $mArmy2->getArmyPositionByArmyIdPlayerId($armyId, $user->parameters['playerId']);
        $armiesIds = $mArmy2->joinArmiesAtPosition($position, $user->parameters['playerId']);

        if (empty($armyId)) {
            $gameHandler->sendError($user, 'Brak "armyId"!');
            return;
        }

        $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);
        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'join',
            'army' => $mArmy2->getArmyByArmyId($armiesIds['armyId']),
            'deletedIds' => $armiesIds['deletedIds'],
            'color' => $playersInGameColors[$user->parameters['playerId']]
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}