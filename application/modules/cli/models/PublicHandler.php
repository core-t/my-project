<?php

/**
 * This resource handler will respond to all messages sent to /public on the socketserver below
 *
 * All this handler does is receiving data from browsers and sending the responds back
 * @author Bartosz Krzeszewski
 *
 */
class Cli_Model_PublicHandler extends Cli_WofHandler
{

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg)
    {

        $dataIn = Zend_Json::decode($msg->getData());
//        print_r('ZAPYTANIE ');
//        print_r($dataIn);

        $db = Cli_Model_Database::getDb();

        if ($dataIn['type'] == 'register') {
            if (!isset($dataIn['gameId']) || !isset($dataIn['playerId'])) {
                $this->sendError($user, 'Brak "gameId" lub "playerId"');
                return;
            }

            $mPlayersInGame = new Application_Model_PlayersInGame($dataIn['gameId'], $db);

            if (!$mPlayersInGame->checkAccessKey($dataIn['playerId'], $dataIn['accessKey'], $db)) {
                $this->sendError($user, 'Brak uprawnień!');
                return;
            }

            $user->parameters = array(
                'gameId' => $dataIn['gameId'],
                'playerId' => $dataIn['playerId']
            );

            $mPlayersInGame->updatePlayerInGameWSSUId($dataIn['playerId'], $user->getId());
            $this->update($dataIn['gameId'], $db);

            $mGame = new Application_Model_Game($user->parameters['gameId'], $db);

            $mapId = $mGame->getMapId();

            $mMapPlayers = new Application_Model_MapPlayers($mapId, $db);
            Zend_Registry::set('mapPlayerIdToShortNameRelations', $mMapPlayers->getMapPlayerIdToShortNameRelations());

            return;
        }

        if (!Zend_Validate::is($user->parameters['gameId'], 'Digits') || !Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $this->sendError($user, 'Brak "gameId" lub "playerId". Brak autoryzacji.');
            return;
        }

        switch ($dataIn['type']) {
            case 'start':
                $mGame = new Application_Model_Game($user->parameters['gameId'], $db);

                if (!$mGame->isGameMaster($user->parameters['playerId'])) {
                    echo('Not game master!');
                    return;
                }

                $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
                $mPlayersInGame->disconnectNotActive();

                $mGame->startGame($mPlayersInGame->getPlayerIdByColor('white'));
                $players = $mPlayersInGame->getAll();

                $mapId = $mGame->getMapId();

                $mMapCastles = new Application_Model_MapCastles($mapId, $db);
//                $mMapPlayers = new Application_Model_MapPlayers($mapId, $db);

//                $playerColors = $mMapPlayers->getMapPlayerIds();
                $startPositions = $mMapCastles->getDefaultStartPositions();

                foreach ($players as $player) {
                    $mHero = new Application_Model_Hero($player['playerId'], $db);
                    $playerHeroes = $mHero->getHeroes();
                    if (empty($playerHeroes)) {
                        $mHero->createHero();
                        $playerHeroes = $mHero->getHeroes($player['playerId'], $db);
                    }
                    $mArmy = new Application_Model_Army($user->parameters['gameId'], $db);

                    $armyId = $mArmy->createArmy($startPositions[$player['mapCastleId']], $player['playerId']);

                    $mHeroesInGame = new Application_Model_HeroesInGame($user->parameters['gameId'], $db);
                    $mHeroesInGame->add($armyId, $playerHeroes[0]['heroId']);

                    $mCastlesInGame = new Application_Model_CastlesInGame($user->parameters['gameId'], $db);
                    $mCastlesInGame->addCastle($player['mapCastleId'], $player['playerId']);
                }

                $token = array('type' => 'start');

                $this->sendToChannel($db, $token, $user->parameters['gameId']);
                break;

            case 'change':
                $mapPlayerId = $dataIn['mapPlayerId'];

                if (empty($mapPlayerId)) {
                    echo('Brak mapPlayerId!');
                    return;
                }

                $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
                $mGame = new Application_Model_Game($user->parameters['gameId'], $db);

                if ($mPlayersInGame->getMapPlayerIdByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db) == $mapPlayerId) { // unselect
                    $mPlayersInGame->updatePlayerReady($user->parameters['playerId'], $mapPlayerId);
                } elseif (!$mPlayersInGame->isNoComputerColorInGame($mapPlayerId)) { // select
                    if ($mPlayersInGame->isColorInGame($mapPlayerId)) {
                        $mPlayersInGame->updatePlayerReady($mPlayersInGame->getPlayerIdByMapPlayerId($mapPlayerId), $mapPlayerId);
                    }
                    $mPlayersInGame->updatePlayerReady($user->parameters['playerId'], $mapPlayerId);
                } elseif ($mGame->isGameMaster($user->parameters['playerId'])) { // kick
                    $mPlayersInGame->updatePlayerReady($mPlayersInGame->getPlayerIdByMapPlayerId($mapPlayerId), $mapPlayerId);
                } else {
                    echo('Błąd!');
                    return;
                }

                $this->update($user->parameters['gameId'], $db);
                break;

            case 'computer':
                $mapPlayerId = $dataIn['mapPlayerId'];

                if (empty($mapPlayerId)) {
                    echo('Brak mapPlayerId!');
                    return;
                }

                $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
                if (!$mGame->isGameMaster($user->parameters['playerId'])) {
                    echo('Brak uprawnień!');
                    return;
                }

                $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);

                if ($mPlayersInGame->isColorInGame($mapPlayerId)) {
                    echo('Ten kolor jest już w grze!');
                    return;
                }

                $playerId = $mPlayersInGame->getComputerPlayerId();

                if (!$playerId) {
                    $mPlayer = new Application_Model_Player($db);
                    $playerId = $mPlayer->createComputerPlayer();

                    $mHero = new Application_Model_Hero($playerId, $db);
                    $mHero->createHero();
                }

                if (!$mPlayersInGame->isPlayerInGame($playerId)) {
                    $mPlayersInGame->joinGame($playerId);
                }
                $mPlayersInGame->updatePlayerReady($playerId, $mapPlayerId);

                $this->update($user->parameters['gameId'], $db);
                break;
        }
    }

    public function onDisconnect(IWebSocketConnection $user)
    {
        if (!isset($user->parameters['gameId']) || !isset($user->parameters['playerId'])) {
            return;
        }
        if (!Zend_Validate::is($user->parameters['gameId'], 'Digits') || !Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            return;
        }

        $db = Cli_Model_Database::getDb();

        $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
        if ($mGame->isGameStarted()) {
            return;
        }

        $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
        $mPlayersInGame->disconnectFromGame($user->parameters['playerId']);

        $mGame->setNewGameMaster($mPlayersInGame->findNewGameMaster());
        $this->update($user->parameters['gameId'], $db);
    }

    private function update($gameId, $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
        $token = $mPlayersInGame->getPlayersWaitingForGame();
        $mGame = new Application_Model_Game($gameId, $db);
        $token['gameMasterId'] = $mGame->getGameMasterId();
        $token['type'] = 'update';

        $this->sendToChannel($db, $token, $gameId);
    }

}
