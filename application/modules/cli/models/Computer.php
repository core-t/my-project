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

        $mPlayer = new Application_Model_Player($db);
        if (!$mPlayer->isComputer($playerId)) {
            $gameHandler->sendError($user, 'To nie komputer!');
            return;
        }

        $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
        if (!$mPlayersInGame->playerTurnActive($playerId)) {
            $mTurn = new Cli_Model_Turn($user->parameters['gameId'], $db, $gameHandler);
            $mTurn->start($playerId, true);
        } else {
            if (Cli_Model_ComputerMainBlocks::handleHeroResurrection($user->parameters['gameId'], $playerId, $db, $gameHandler)) {
                return;
            }

            $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);
            $army = $mArmy2->getComputerArmyToMove($playerId);
            if (!empty($army['armyId'])) {
                $token = Cli_Model_ComputerMainBlocks::moveArmy($user->parameters['gameId'], $playerId, new Cli_Model_Army($army), $db, $user, $gameHandler);
                $token['type'] = 'computer';
                $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
            } else {
                $mTurn = new Cli_Model_Turn($user->parameters['gameId'], $db, $gameHandler);
                $mTurn->next($playerId);
            }
        }
    }

}