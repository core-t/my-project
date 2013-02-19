<?php

class Cli_HeroResurrection {

    public function __construct($castleId, $user, $db, $gameHandler) {
        if ($castleId == null) {
            $gameHandler->sendError($user, 'Brak "castleId"!');
            return;
        }

        if (!Cli_Database::isPlayerCastle($user->parameters['gameId'], $castleId, $user->parameters['playerId'], $db)) {
            $gameHandler->sendError($user, 'To nie jest Twój zamek! ' . $castleId);
            return;
        }

        if (!Cli_Database::isHeroInGame($user->parameters['gameId'], $user->parameters['playerId'], $db)) {
            Cli_Database::connectHero($user->parameters['gameId'], $user->parameters['playerId'], $db);
        }

        $heroId = Cli_Database::getDeadHeroId($user->parameters['gameId'], $user->parameters['playerId'], $db);

        if (!$heroId) {
            $gameHandler->sendError($user, 'Twój heros żyje! ' . $heroId);
            return;
        }

        $gold = Cli_Database::getPlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $db);

        if ($gold < 100) {
            $gameHandler->sendError($user, 'Za mało złota!');
            return;
        }

        $position = Application_Model_Board::getCastlePosition($castleId);
        $armyId = Cli_Database::heroResurection($user->parameters['gameId'], $heroId, $position, $user->parameters['playerId'], $db);
        $gold -= 100;
        Cli_Database::updatePlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $gold, $db);

        $token = array(
            'type' => 'heroResurrection',
            'data' => array(
                'army' => Cli_Database::getArmyByArmyId($user->parameters['gameId'], $armyId, $db),
                'gold' => $gold
            ),
            'color' => Cli_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db)
        );

        $gameHandler->sendToChannel($token, Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db));
    }

}
