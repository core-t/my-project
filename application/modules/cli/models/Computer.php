<?php

class Cli_Model_Computer
{

    public function __construct($user, $db, $gameHandler)
    {
        $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
        if (!$mGame->isGameMaster($user->parameters['playerId'])) {
            $gameHandler->sendError($user, 'Nie Twoja gra!');
            return;
        }

        $playerId = $mGame->getTurnPlayerId();

        if (!Cli_Model_Database::isComputer($playerId, $db)) {
            $gameHandler->sendError($user, 'To nie komputer!');
            return;
        }

        $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
        if (!$mPlayersInGame->playerTurnActive($playerId)) {
            $mTurn = new Cli_Model_Turn($user->parameters['gameId'], $db);
            $token = $mTurn->start($playerId, true);
        } else {
            $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);
            $army = $mArmy2->getComputerArmyToMove($playerId);
            if (!empty($army['armyId'])) {
                $token = Cli_Model_ComputerMainBlocks::moveArmy($user->parameters['gameId'], $playerId, new Cli_Model_Army($army), $db);
            } else {
                $mTurn = new Cli_Model_Turn($user->parameters['gameId'], $db);
                $token = $mTurn->next($playerId);
                $token['action'] = 'end';
            }
        }

        switch ($token['action']) {
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

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}