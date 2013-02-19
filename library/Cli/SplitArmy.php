<?php

class Cli_SplitArmy {

    function __construct($parentArmyId, $s, $h, $user, $db, $gameHandler) {
        if (empty($parentArmyId) || (empty($h) && empty($s))) {
            $gameHandler->sendError($user, 'Brak "armyId", "s" lub "h"!');
            return;
        }

        $heroesIds = explode(',', $h);
        $soldiersIds = explode(',', $s);

        if ((isset($heroesIds[0]) && !empty($heroesIds[0])) || (isset($soldiersIds) && !empty($soldiersIds))) {
            $position = Cli_Database::getArmyPositionByArmyId($user->parameters['gameId'], $parentArmyId, $user->parameters['playerId'], $db);
            $childArmyId = Cli_Database::createArmy($user->parameters['gameId'], $db, array('x' => $position['x'], 'y' => $position['y']), $user->parameters['playerId']);
            foreach ($heroesIds as $heroId)
            {
                if (!Zend_Validate::is($heroId, 'Digits')) {
                    continue;
                }
                if (!Cli_Database::isHeroInArmy($user->parameters['gameId'], $parentArmyId, $user->parameters['playerId'], $heroId, $db)) {
                    continue;
                }
                Cli_Database::heroUpdateArmyId($user->parameters['gameId'], $heroId, $childArmyId, $db);
            }
            foreach ($soldiersIds as $soldierId)
            {
                if (!Zend_Validate::is($soldierId, 'Digits')) {
                    continue;
                }
                if (!Cli_Database::isSoldierInArmy($user->parameters['gameId'], $parentArmyId, $user->parameters['playerId'], $soldierId, $db)) {
                    continue;
                }
                Cli_Database::soldierUpdateArmyId($user->parameters['gameId'], $soldierId, $childArmyId, $db);
            }
        }

        if (empty($childArmyId)) {
            $gameHandler->sendError($user, 'Brak "childArmyId"');
            return;
        }

        $token = array(
            'type' => 'splitArmy',
            'parentArmy' => Cli_Database::getArmyByArmyId($user->parameters['gameId'], $parentArmyId, $db),
            'childArmy' => Cli_Database::getArmyByArmyId($user->parameters['gameId'], $childArmyId, $db),
            'color' => Cli_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db)
        );

        $gameHandler->sendToChannel($token, Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db));
    }

}