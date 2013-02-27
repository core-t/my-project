<?php

class Cli_Model_Computer {

    public function __construct($user, $db, $gameHandler) {
        if (!Cli_Model_Database::isGameMaster($user->parameters['gameId'], $user->parameters['playerId'], $db)) {
            $gameHandler->sendError($user, 'Nie Twoja gra!');
            return;
        }

        $playerId = Cli_Model_Database::getTurnPlayerId($user->parameters['gameId'], $db);

        if (!Cli_Model_Database::isComputer($playerId, $db)) {
            $gameHandler->sendError($user, 'To nie komputer!');
            return;
        }

        if (!Cli_Model_Database::playerTurnActive($user->parameters['gameId'], $playerId, $db)) {
            $token = Cli_Model_ComputerMainBlocks::startTurn($user->parameters['gameId'], $playerId, $db);
        } else {
            $army = Cli_Model_Database::getComputerArmyToMove($user->parameters['gameId'], $playerId, $db);
            if (!empty($army['armyId'])) {
                $token = Cli_Model_ComputerMainBlocks::moveArmy($user->parameters['gameId'], $playerId, new Cli_Model_Army($army), $db);
            } else {
                $token = Cli_Model_Turn::next($user->parameters['gameId'], $playerId, $db);
                $token['action'] = 'end';
            }
        }

        switch ($token['action'])
        {
            case 'continue':
                $token['type'] = 'computer';
                break;
            case 'start':
                $token['type'] = 'computerStart';
                break;
            case 'end':
                $token['type'] = 'nextTurn';
                break;
            case 'gameover':
                $token['type'] = 'computerGameover';
                break;
        }

        $gameHandler->sendToChannel($token, Cli_Model_Database::getInGameWSSUIds($user->parameters['gameId'], $db));
    }

}