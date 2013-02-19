<?php

class Cli_Chat {

    public function __construct($msg, $user, $db, $gameHandler) {
        Cli_Database::insertChatMessage($user->parameters['gameId'], $user->parameters['playerId'], $msg, $db);

        $token = array(
            'type' => 'chat',
            'msg' => $msg,
            'color' => Cli_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db)
        );

        $gameHandler->sendToChannel($token, Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $user->parameters['playerId'], $db));
    }

}