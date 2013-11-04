<?php

class Cli_Model_SplitArmy
{

    function __construct($parentArmyId, $s, $h, $user, $db, $gameHandler)
    {
        if (empty($parentArmyId) || (empty($h) && empty($s))) {
            $gameHandler->sendError($user, 'Brak "armyId", "s" lub "h"!');
            return;
        }

        $heroesIds = explode(',', $h);
        $soldiersIds = explode(',', $s);

        $childArmyId = null;

        if ((isset($heroesIds[0]) && !empty($heroesIds[0])) || (isset($soldiersIds) && !empty($soldiersIds))) {
            foreach ($heroesIds as $heroId) {
                if (!Zend_Validate::is($heroId, 'Digits')) {
                    continue;
                }
                if (!Cli_Model_Database::isHeroInArmy($user->parameters['gameId'], $parentArmyId, $user->parameters['playerId'], $heroId, $db)) {
                    continue;
                }

                if (empty($childArmyId)) {
                    $position = Cli_Model_Database::getArmyPositionByArmyId($user->parameters['gameId'], $parentArmyId, $user->parameters['playerId'], $db);
                    $mArmy = new Application_Model_Army($user->parameters['gameId'], $db);
                    $childArmyId = $mArmy->createArmy($position, $user->parameters['playerId']);
                }
                Cli_Model_Database::heroUpdateArmyId($user->parameters['gameId'], $heroId, $childArmyId, $db);
            }
            foreach ($soldiersIds as $soldierId) {
                if (!Zend_Validate::is($soldierId, 'Digits')) {
                    continue;
                }
                if (!Cli_Model_Database::isSoldierInArmy($user->parameters['gameId'], $parentArmyId, $user->parameters['playerId'], $soldierId, $db)) {
                    continue;
                }

                if (empty($childArmyId)) {
                    $position = Cli_Model_Database::getArmyPositionByArmyId($user->parameters['gameId'], $parentArmyId, $user->parameters['playerId'], $db);
                    $mArmy = new Application_Model_Army($user->parameters['gameId'], $db);
                    $childArmyId = $mArmy->createArmy($position, $user->parameters['playerId']);
                }
                Cli_Model_Database::soldierUpdateArmyId($user->parameters['gameId'], $soldierId, $childArmyId, $db);
            }
        }

        if (empty($childArmyId)) {
            $gameHandler->sendError($user, 'Brak "childArmyId"');
            return;
        }

        $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);
        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'splitArmy',
            'parentArmy' => $mArmy2->getArmyByArmyId($parentArmyId),
            'childArmy' => $mArmy2->getArmyByArmyId($childArmyId),
            'color' => $playersInGameColors[$user->parameters['playerId']]
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}