<?php

class Cli_Model_Chat
{

    public function __construct($msg, $user, $db, $gameHandler)
    {
        $mChat = new Application_Model_Chat($user->parameters['gameId'], $db);
        $mChat->insertChatMessage($user->parameters['playerId'], $msg);

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'chat',
            'msg' => $msg,
            'color' => $playersInGameColors[$user->parameters['playerId']]
        );

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}