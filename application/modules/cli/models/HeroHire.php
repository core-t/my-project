<?php

class Cli_Model_HeroHire
{
    public function __construct($user, $db, $gameHandler)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
        $gold = $mPlayersInGame->getPlayerGold($user->parameters['playerId']);

        if ($gold < 1000) {
            $gameHandler->sendError($user, 'Za mało złota!');
            return;
        }

        $capitals = Zend_Registry::get('capitals');
        $playersInGameColors = Zend_Registry::get('playersInGameColors');
        $color = $playersInGameColors[$user->parameters['playerId']];
        $castleId = $capitals[$color];

        $mCastlesInGame = new Application_Model_CastlesInGame($user->parameters['gameId'], $db);
        if (!$mCastlesInGame->isPlayerCastle($castleId, $user->parameters['playerId'])) {
            $gameHandler->sendError($user, 'Aby wynająć herosa musisz posiadać stolicę!');
            return;
        }

        $mHero = new Application_Model_Hero($user->parameters['playerId'], $db);
        $heroId = $mHero->createHero();

        $mHeroesInGame = new Application_Model_HeroesInGame($user->parameters['gameId'], $db);
        $mHeroesInGame->connectHero($heroId);

        $mapCastles = Zend_Registry::get('castles');
        $armyId = Cli_Model_Army::heroResurrection($user->parameters['gameId'], $heroId, $mapCastles[$castleId]['position'], $user->parameters['playerId'], $db);
        $gold -= 1000;
        $mPlayersInGame->updatePlayerGold($user->parameters['playerId'], $gold);

        $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);

        $token = array(
            'type' => 'resurrection',
            'data' => array(
                'army' => $mArmy2->getArmyByArmyId($armyId),
                'gold' => $gold
            ),
            'color' => $color
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }
}
