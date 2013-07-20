<?php

class Cli_Model_HeroResurrection {

    public function __construct($castleId, $user, $db, $gameHandler) {
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

        $heroId = Cli_Model_Database::getDeadHeroId($user->parameters['gameId'], $user->parameters['playerId'], $db);

        if (!$heroId) {
            $gameHandler->sendError($user, 'Twój heros żyje! ' . $heroId);
            return;
        }

        $gold = Cli_Model_Database::getPlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $db);

        if ($gold < 100) {
            $gameHandler->sendError($user, 'Za mało złota!');
            return;
        }

        $position = Application_Model_Board::getCastlePosition($castleId);
        $armyId = Cli_Model_Database::heroResurection($user->parameters['gameId'], $heroId, $position, $user->parameters['playerId'], $db);
        $gold -= 100;
        Cli_Model_Database::updatePlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $gold, $db);

        $token = array(
            'type' => 'heroResurrection',
            'data' => array(
                'army' => Cli_Model_Database::getArmyByArmyId($user->parameters['gameId'], $armyId, $db),
                'gold' => $gold
            ),
            'color' => Cli_Model_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db)
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}
