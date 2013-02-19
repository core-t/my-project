<?php

class Cli_Model_JoinArmy {

    public function __construct($armyId, $user, $db, $gameHandler) {
        if (empty($armyId)) {
            $gameHandler->sendError($user, 'Brak "armyId"!');
            return;
        }

        $position = Cli_Model_Database::getArmyPositionByArmyId($user->parameters['gameId'], $armyId, $user->parameters['playerId'], $db);
        $armiesIds = Cli_Model_Database::joinArmiesAtPosition($user->parameters['gameId'], $position, $user->parameters['playerId'], $db);

        if (empty($armyId)) {
            $gameHandler->sendError($user, 'Brak "armyId"!');
            return;
        }
        $token = array(
            'type' => 'joinArmy',
            'army' => Cli_Model_Database::getArmyByArmyId($user->parameters['gameId'], $armiesIds['armyId'], $db),
            'deletedIds' => $armiesIds['deletedIds'],
            'color' => Cli_Model_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db)
        );

        $users = Cli_Model_Database::getInGameWSSUIds($user->parameters['gameId'], $db);

        $gameHandler->sendToChannel($token, $users);
    }

}