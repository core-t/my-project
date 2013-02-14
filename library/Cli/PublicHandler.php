<?php

/**
 * This resource handler will respond to all messages sent to /public on the socketserver below
 *
 * All this handler does is receiving data from browsers and sending the responds back
 * @author Bartosz Krzeszewski
 *
 */
class Cli_PublicHandler extends Cli_WofHandler {

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {

        $dataIn = Zend_Json::decode($msg->getData());
//        print_r('ZAPYTANIE ');
//        print_r($dataIn);

        $db = Cli_Database::getDb();

        if ($dataIn['type'] == 'register') {
            if (!isset($dataIn['gameId']) || !isset($dataIn['playerId'])) {
                $this->sendError($user, 'Brak "gameId" lub "playerId"');
                return;
            }
            if (!Cli_Database::checkAccessKey($dataIn['gameId'], $dataIn['playerId'], $dataIn['accessKey'], $db)) {
                $this->sendError($user, 'Brak uprawnień!');
                return;
            }

            $user->parameters = array(
                'gameId' => $dataIn['gameId'],
                'playerId' => $dataIn['playerId']
            );

            Cli_Database::updatePlayerInGameWSSUId($dataIn['gameId'], $dataIn['playerId'], $user->getId(), $db);
            $this->update($dataIn['gameId'], $db);
            return;
        }

        if (!Zend_Validate::is($user->parameters['gameId'], 'Digits') || !Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $this->sendError($user, 'Brak "gameId" lub "playerId". Brak autoryzacji.');
            return;
        }

        switch ($dataIn['type'])
        {
            case 'start':
                if (Cli_Database::isGameMaster($user->parameters['gameId'], $user->parameters['playerId'], $db)) {
                    Cli_Database::disconnectNotActive($user->parameters['gameId'], $db);
                    Cli_Database::startGame($user->parameters['gameId'], $db);
                    $computerPlayers = Cli_Database::getComputerPlayers($user->parameters['gameId'], $db);
                    foreach ($computerPlayers as $computer)
                    {
                        $startPositions = Application_Model_Board::getDefaultStartPositions();
                        $playerHeroes = Cli_Database::getHeroes($computer['playerId'], $db);
                        if (empty($playerHeroes)) {
                            Cli_Database::createHero($computer['playerId'], $db);
                            $playerHeroes = Cli_Database::getHeroes($computer['playerId'], $db);
                        }
                        $armyId = Cli_Database::createArmy(
                                        $user->parameters['gameId'], $db, $startPositions[$computer['color']]['position'], $computer['playerId']);
                        Cli_Database::addHeroToGame($user->parameters['gameId'], $armyId, $playerHeroes[0]['heroId'], $db);
                        Cli_Database::addCastle($user->parameters['gameId'], $startPositions[$computer['color']]['id'], $computer['playerId'], $db);
                    }

                    $token = array('type' => 'start');

                    $this->sendToChannel($token, Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db));
                }
                break;

            case 'change':
                $color = $dataIn['color'];

                if (empty($color)) {
                    echo('Brak color!');
                    return;
                }

                if (Cli_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db) == $color) { // unselect
                    Cli_Database::updatePlayerReady($user->parameters['gameId'], $user->parameters['playerId'], $color, $db);
                } elseif (!Cli_Database::isColorInGame($user->parameters['gameId'], $color, $db)) { // select
                    Cli_Database::updatePlayerReady($user->parameters['gameId'], $user->parameters['playerId'], $color, $db);
                } elseif (Cli_Database::isGameMaster($user->parameters['gameId'], $user->parameters['playerId'], $db)) { // kick
                    Cli_Database::updatePlayerReady($user->parameters['gameId'], Cli_Database::getPlayerIdByColor($user->parameters['gameId'], $color, $db), $color, $db);
                } else {
                    echo('Błąd!');
                    return;
                }

                $this->update($user->parameters['gameId'], $db);
                break;

            case 'computer':
                $color = $dataIn['color'];

                if (empty($color)) {
                    echo('Brak color!');
                    return;
                }

                if (!Cli_Database::isGameMaster($user->parameters['gameId'], $user->parameters['playerId'], $db)) {
                    echo('Brak uprawnień!');
                    return;
                }

                if (Cli_Database::isColorInGame($user->parameters['gameId'], $color, $db)) {
                    echo('Ten kolor jest już w grze!');
                    return;
                }

                $playerId = Cli_Database::getComputerPlayerId($user->parameters['gameId'], $db);

                if (!$playerId) {
                    $playerId = Cli_Database::createComputerPlayer($db);
                    Cli_Database::createHero($playerId, $db);
                }

                if (!Cli_Database::isPlayerInGame($user->parameters['gameId'], $playerId, $db)) {
                    Cli_Database::joinGame($user->parameters['gameId'], $playerId, $db);
                }
                Cli_Database::updatePlayerReady($user->parameters['gameId'], $playerId, $color, $db);

                $this->update($user->parameters['gameId'], $db);
                break;
        }
    }

    public function onDisconnect(IWebSocketConnection $user) {
        if (!Zend_Validate::is($user->parameters['gameId'], 'Digits') || !Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
//            $this->sendError($user, 'Brak "gameId" lub "playerId". Brak autoryzacji.');
            return;
        }
        $db = Cli_Database::getDb();
        if (Cli_Database::isGameStarted($user->parameters['gameId'], $db)) {
            return;
        }

        Cli_Database::disconnectFromGame($user->parameters['gameId'], $user->parameters['playerId'], $db);
        Cli_Database::findNewGameMaster($user->parameters['gameId'], $db);
        $this->update($user->parameters['gameId'], $db);
    }

    private function update($gameId, $db) {
        $token = Cli_Database::getPlayersWaitingForGame($gameId, $db);
        $token['gameMasterId'] = Cli_Database::getGameMasterId($gameId, $db);
        $token['type'] = 'update';

        $this->sendToChannel($token, Cli_Database::getInGameWSSUIds($gameId, $db));
    }

}
