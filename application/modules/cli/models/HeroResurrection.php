<?php

class Cli_Model_HeroResurrection
{

    public function __construct($castleId, $user, $db, $gameHandler)
    {
        if ($castleId == null) {
            $gameHandler->sendError($user, 'Brak "castleId"!');
            return;
        }

        if (!Cli_Model_Database::isPlayerCastle($user->parameters['gameId'], $castleId, $user->parameters['playerId'], $db)) {
            $gameHandler->sendError($user, 'To nie jest Twój zamek! ' . $castleId);
            return;
        }

        if (!Cli_Model_Database::isHeroInGame($user->parameters['gameId'], $user->parameters['playerId'], $db)) {
            Cli_Model_Database::connectHero($user->parameters['gameId'], $user->parameters['playerId'], $db);
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($user->parameters['gameId'], $db);
        $heroId = $mHeroesInGame->getDeadHeroId($user->parameters['playerId']);

        if (!$heroId) {
            $gameHandler->sendError($user, 'Twój heros żyje! ' . $heroId);
            return;
        }

        $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
        $gold = $mPlayersInGame->getPlayerInGameGold($user->parameters['playerId']);

        if ($gold < 100) {
            $gameHandler->sendError($user, 'Za mało złota!');
            return;
        }

        $mapCastles = Zend_Registry::get('castles');
        $armyId = Cli_Model_Database::heroResurrection($user->parameters['gameId'], $heroId, $mapCastles[$castleId]['position'], $user->parameters['playerId'], $db);
        $gold -= 100;
        $mPlayersInGame->updatePlayerInGameGold($user->parameters['playerId'], $gold);

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'heroResurrection',
            'data' => array(
                'army' => Cli_Model_Database::getArmyByArmyId($user->parameters['gameId'], $armyId, $db),
                'gold' => $gold
            ),
            'color' => $playersInGameColors[$user->parameters['playerId']]
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }


}
