<?php

class Cli_Model_Chat {

    public function __construct($msg, $user, $db, $gameHandler) {
        Cli_Model_Database::insertChatMessage($user->parameters['gameId'], $user->parameters['playerId'], $msg, $db);

        $token = array(
            'type' => 'chat',
            'msg' => $msg,
            'color' => Cli_Model_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db)
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}