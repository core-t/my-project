<?php

class Cli_Model_HeroResurrection
{

    public function __construct($user, $db, $gameHandler)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
        $gold = $mPlayersInGame->getPlayerGold($user->parameters['playerId']);

        if ($gold < 100) {
            $gameHandler->sendError($user, 'Za mało złota!');
            return;
        }

        $capitals = Zend_Registry::get('capitals');
        $playersInGameColors = Zend_Registry::get('playersInGameColors');
        $color = $playersInGameColors[$user->parameters['playerId']];
        $castleId = $capitals[$color];

        $mCastlesInGame = new Application_Model_CastlesInGame($user->parameters['gameId'], $db);
        if (!$mCastlesInGame->isPlayerCastle($castleId, $user->parameters['playerId'])) {
            $gameHandler->sendError($user, 'Aby wskrzesić herosa musisz posiadać stolicę!');
            return;
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($user->parameters['gameId'], $db);
        $heroId = $mHeroesInGame->getDeadHeroId($user->parameters['playerId']);

        if (!$heroId) {
            $gameHandler->sendError($user, 'Twój heros żyje! ' . $heroId);
            return;
        }

        $mapCastles = Zend_Registry::get('castles');
        $armyId = Cli_Model_Army::heroResurrection($user->parameters['gameId'], $heroId, $mapCastles[$castleId]['position'], $user->parameters['playerId'], $db);
        $gold -= 100;
        $mPlayersInGame->updatePlayerGold($user->parameters['playerId'], $gold);

        $token = array(
            'type' => 'resurrection',
            'data' => array(
                'army' => Cli_Model_Army::getArmyByArmyId($armyId, $user->parameters['gameId'], $db),
                'gold' => $gold
            ),
            'color' => $color
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }


}
