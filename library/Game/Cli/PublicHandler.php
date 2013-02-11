<?php

/**
 * This resource handler will respond to all messages sent to /public on the socketserver below
 *
 * All this handler does is receiving data from browsers and sending the responds back
 * @author Bartosz Krzeszewski
 *
 */
class Game_Cli_PublicHandler extends Game_Cli_WofHandler {

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {

        $dataIn = Zend_Json::decode($msg->getData());
        print_r('ZAPYTANIE ');
        print_r($dataIn);

        $db = Game_Cli_Database::getDb();

        if (!isset($dataIn['gameId']) || !isset($dataIn['playerId'])) {
            $this->sendError($user, 'Brak "gameId" lub "playerId"');
            return;
        }

        if (!Game_Cli_Database::checkAccessKey($dataIn['gameId'], $dataIn['playerId'], $dataIn['accessKey'], $db)) {
            $this->sendError($user, 'Brak uprawnień!');
            return;
        }


        switch ($dataIn['type'])
        {
            case 'start':
                if (Game_Cli_Database::isGameMaster($dataIn['gameId'], $dataIn['playerId'], $db)) {
                    Game_Cli_Database::disconnectNotActive($dataIn['gameId'], $db);
                    Game_Cli_Database::startGame($dataIn['gameId'], $db);
                    $computerPlayers = Game_Cli_Database::getComputerPlayers($dataIn['gameId'], $db);
                    foreach ($computerPlayers as $computer)
                    {
                        $startPositions = Application_Model_Board::getDefaultStartPositions();
                        $playerHeroes = Game_Cli_Database::getHeroes($computer['playerId'], $db);
                        if (empty($playerHeroes)) {
                            Game_Cli_Database::createHero($computer['playerId'], $db);
                            $playerHeroes = Game_Cli_Database::getHeroes($computer['playerId'], $db);
                        }
                        $armyId = Game_Cli_Database::createArmy(
                                        $dataIn['gameId'], $db, $startPositions[$computer['color']]['position'], $computer['playerId']);
                        Game_Cli_Database::addHeroToGame($dataIn['gameId'], $armyId, $playerHeroes[0]['heroId'], $db);
                        Game_Cli_Database::addCastle($dataIn['gameId'], $startPositions[$computer['color']]['id'], $computer['playerId'], $db);
                    }

                    $token = array('type' => 'start');

                    $this->sendToChannel($token, Game_Cli_Database::getInGameWSSUIds($dataIn['gameId'], $db));
                }
                break;

            case 'register':
                Game_Cli_Database::updatePlayerInGameWSSUId($dataIn['gameId'], $dataIn['playerId'], $user->getId(), $db);
                $this->update($dataIn, $db);
                break;

            case 'change':
                $color = $dataIn['color'];

                if (empty($color)) {
                    echo('Brak color!');
                    return;
                }

                if (Game_Cli_Database::getColorByPlayerId($dataIn['gameId'], $dataIn['playerId'], $db) == $color) { // unselect
                    Game_Cli_Database::updatePlayerReady($dataIn['gameId'], $dataIn['playerId'], $color, $db);

                    var_dump('aaa');
                } elseif (!Game_Cli_Database::isColorInGame($dataIn['gameId'], $color, $db)) { // select
                    Game_Cli_Database::updatePlayerReady($dataIn['gameId'], $dataIn['playerId'], $color, $db);

                    var_dump('bbb');
                } elseif (Game_Cli_Database::isGameMaster($dataIn['gameId'], $dataIn['playerId'], $db)) { // kick
                    Game_Cli_Database::updatePlayerReady($dataIn['gameId'], Game_Cli_Database::getPlayerIdByColor($dataIn['gameId'], $color, $db), $color, $db);

                    var_dump('ccc');
                } else {
                    echo('Błąd!');
                    return;
                }

                $this->update($dataIn, $db);
                break;

            case 'computer':
                $color = $dataIn['color'];

                if (empty($color)) {
                    echo('Brak color!');
                    return;
                }

                if (!Game_Cli_Database::isGameMaster($dataIn['gameId'], $dataIn['playerId'], $db)) {
                    echo('Brak uprawnień!');
                    return;
                }

                if (Game_Cli_Database::isColorInGame($dataIn['gameId'], $color, $db)) {
                    echo('Ten kolor jest już w grze!');
                    return;
                }

                $playerId = Game_Cli_Database::getComputerPlayerId($dataIn['gameId'], $db);

                if (!$playerId) {
                    $playerId = Game_Cli_Database::createComputerPlayer($db);
                    Game_Cli_Database::createHero($playerId, $db);
                }

                Game_Cli_Database::joinGame($dataIn['gameId'], $playerId, $db);
                Game_Cli_Database::updatePlayerReady($dataIn['gameId'], $playerId, $color, $db);

                $this->update($dataIn, $db);
                break;
        }
    }

    public function onDisconnect(IWebSocketConnection $user) {
        $this->say("[DEMO] {$user->getId()} disconnected");
    }

    public function update($dataIn, $db) {
        $token = Game_Cli_Database::getPlayersWaitingForGame($dataIn['gameId'], $db);
        $token['gameMasterId'] = Game_Cli_Database::getGameMasterId($dataIn['gameId'], $db);
        $token['type'] = 'update';

        $this->sendToChannel($token, Game_Cli_Database::getInGameWSSUIds($dataIn['gameId'], $db));
    }

}
