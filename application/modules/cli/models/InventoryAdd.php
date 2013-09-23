<?php

class Cli_Model_InventoryAdd
{

    public function __construct($heroId, $user, $db, $gameHandler)
    {
        if ($heroId == null) {
            $gameHandler->sendError($user, 'Brak "heroId"!');
            return;
        }

        if (!Cli_Model_Database::isPlayerCastle($user->parameters['gameId'], $castleId, $user->parameters['playerId'], $db)) {
            $gameHandler->sendError($user, 'To nie jest Twój zamek.');
            return;
        }

        $token = array(
            'type' => 'inventory',
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}