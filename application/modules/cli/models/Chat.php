<?php

class Cli_Model_Chat {

    public function __construct($msg, $user, $db, $gameHandler) {
        Cli_Model_Database::insertChatMessage($user->parameters['gameId'], $user->parameters['playerId'], $msg, $db);

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'chat',
            'msg' => $msg,
            'color' => $playersInGameColors[$user->parameters['playerId']]
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}