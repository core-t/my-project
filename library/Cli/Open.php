<?php

class Cli_Open {

    public function __construct($dataIn, $user, $db, $gameHandler) {
        if (!isset($dataIn['gameId']) || !isset($dataIn['playerId'])) {
            $gameHandler->sendError($user, 'Brak "gameId" lub "playerId"');
            return;
        }
        if (!Cli_Database::checkAccessKey($dataIn['gameId'], $dataIn['playerId'], $dataIn['accessKey'], $db)) {
            $gameHandler->sendError($user, 'Brak uprawnień!');
            return;
        }

        Cli_Database::updatePlayerInGameWSSUId($dataIn['gameId'], $dataIn['playerId'], $user->getId(), $db);

        $token = array(
            'type' => 'open'
        );

        $gameHandler->send($user, Zend_Json::encode($token));

        return array(
            'gameId' => $dataIn['gameId'],
            'playerId' => $dataIn['playerId']
        );
    }

}