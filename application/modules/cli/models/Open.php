<?php

class Cli_Model_Open
{

    private $_parameters = array();

    public function __construct($dataIn, $user, $db, $gameHandler)
    {
        if (!isset($dataIn['gameId']) || !isset($dataIn['playerId']) || !isset($dataIn['langId'])) {
            $gameHandler->sendError($user, 'Brak "gameId" lub "playerId" lub "langId');
            return;
        }

        $mPlayersInGame = new Application_Model_PlayersInGame($dataIn['gameId'], $db);

        if (!$mPlayersInGame->checkAccessKey($dataIn['playerId'], $dataIn['accessKey'], $db)) {
            $gameHandler->sendError($user, 'Brak uprawnień!');
            return;
        }

        $mPlayersInGame->updatePlayerInGameWSSUId($dataIn['playerId'], $user->getId());

        $token = array(
            'type' => 'open'
        );

        $gameHandler->send($user, Zend_Json::encode($token));

        $this->_parameters = array(
            'gameId' => $dataIn['gameId'],
            'playerId' => $dataIn['playerId'],
            'langId' => $dataIn['langId']
        );
    }

    public function getParameters()
    {
        return $this->_parameters;
    }

}