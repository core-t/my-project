<?php

class Cli_Model_SplitArmy
{

    function split($parentArmyId, $s, $h, $user, $playerId, $db, $gameHandler)
    {
        if (empty($parentArmyId) || (empty($h) && empty($s))) {
            $gameHandler->sendError($user, 'Brak "armyId", "s" lub "h"!');
            return;
        }

        $heroesIds = explode(',', $h);
        $soldiersIds = explode(',', $s);

        $childArmyId = null;

        $mArmy = new Application_Model_Army($user->parameters['gameId'], $db);

        if (isset($heroesIds[0]) && !empty($heroesIds[0])) {

            $mHeroesInGame = new Application_Model_HeroesInGame($user->parameters['gameId'], $db);

            foreach ($heroesIds as $heroId) {
                if (!Zend_Validate::is($heroId, 'Digits')) {
                    continue;
                }
                if (!$mHeroesInGame->isHeroInArmy($parentArmyId, $heroId)) {
                    continue;
                }

                if (empty($childArmyId)) {
                    $position = $mArmy->getArmyPositionByArmyIdPlayerId($parentArmyId, $playerId);
                    $childArmyId = $mArmy->createArmy($position, $playerId);
                }

                $mHeroesInGame->heroUpdateArmyId($heroId, $childArmyId);
            }
        }

        if (isset($soldiersIds) && !empty($soldiersIds)) {

            $mSoldier = new Application_Model_UnitsInGame($user->parameters['gameId'], $db);

            foreach ($soldiersIds as $soldierId) {
                if (!Zend_Validate::is($soldierId, 'Digits')) {
                    continue;
                }

                if (!$mSoldier->isSoldierInArmy($parentArmyId, $soldierId)) {
                    continue;
                }

                if (empty($childArmyId)) {
                    $position = $mArmy->getArmyPositionByArmyIdPlayerId($parentArmyId, $playerId);
                    $childArmyId = $mArmy->createArmy($position, $playerId);
                }

                $mSoldier->soldierUpdateArmyId($soldierId, $childArmyId);
            }
        }

        if (empty($childArmyId)) {
            $gameHandler->sendError($user, 'Brak "childArmyId"');
            return;
        }

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'split',
            'parentArmy' => Cli_Model_Army::getArmyByArmyId($parentArmyId, $user->parameters['gameId'], $db),
            'childArmy' => Cli_Model_Army::getArmyByArmyId($childArmyId, $user->parameters['gameId'], $db),
            'color' => $playersInGameColors[$playerId]
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);

        return $childArmyId;
    }

}