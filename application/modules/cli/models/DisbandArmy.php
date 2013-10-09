<?php

class Cli_Model_DisbandArmy
{

    public function __construct($armyId, $user, $db, $gameHandler)
    {
        if (empty($armyId)) {
            $gameHandler->sendError($user, 'Brak "armyId"!');
            return;
        }

        $mArmy = new Application_Model_Army($user->parameters['gameId'], $db);
        $destroyArmyResponse = $mArmy->destroyArmy($armyId, $user->parameters['playerId']);

        if (!$destroyArmyResponse) {
            $gameHandler->sendError($user, 'Nie mogę usunąć armii!');
            return;
        }

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'disbandArmy',
            'armyId' => $armyId,
            'color' => $playersInGameColors[$user->parameters['playerId']]
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}