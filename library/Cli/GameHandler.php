<?php

/**
 * This resource handler will respond to all messages sent to /game on the socketserver below
 *
 * All this handler does is receiving data from browsers and sending the responds back
 * @author Bartosz Krzeszewski
 *
 */
class Cli_GameHandler extends Cli_WofHandler {

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {

        $dataIn = Zend_Json::decode($msg->getData());
        print_r('ZAPYTANIE ');
        print_r($dataIn);

        $db = Cli_Database::getDb();

        if ($dataIn['type'] == 'open') {
            $user->parameters = new Cli_Open($dataIn, $user, $db, $this);
            return;
        }

        if (!Zend_Validate::is($user->parameters['gameId'], 'Digits') || !Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $this->sendError($user, 'Brak "gameId" lub "playerId". Brak autoryzacji.');
            return;
        }


        if ($dataIn['type'] == 'chat') {
            new Cli_Chat($dataIn['msg'], $user, $db, $this);
            return;
        }

        if ($dataIn['type'] == 'computer') {
            new Cli_Computer($user, $db, $this);
            return;
        }

        if (!Cli_Database::isPlayerTurn($user->parameters['gameId'], $user->parameters['playerId'], $db)) {
            $this->sendError($user, 'Nie Twoja tura.');
            return;
        }

        switch ($dataIn['type'])
        {
            case 'move':
                if (!isset($dataIn['armyId'])) {
                    $this->sendError($user, 'Brak "armyId"!');
                    return;
                }

                if (!isset($dataIn['x'])) {
                    $this->sendError($user, 'Brak "x"!');
                    return;
                }

                if (!isset($dataIn['y'])) {
                    $this->sendError($user, 'Brak "y"!');
                    return;
                }

                new Cli_Move($dataIn['armyId'], $dataIn['x'], $dataIn['y'], $user, $db, $this);
                break;

            case 'splitArmy':
                new Cli_SplitArmy($dataIn['data']['armyId'], $dataIn['data']['s'], $dataIn['data']['h'], $user, $db, $this);
                break;

            case 'joinArmy':
                new Cli_JoinArmy($dataIn['data']['armyId'], $user, $db, $this);
                break;

            case 'fortifyArmy':
                $armyId = $dataIn['armyId'];
                if (empty($armyId)) {
                    $this->sendError($user, 'Brak "armyId"!');
                    return;
                }

                Cli_Database::fortifyArmy($user->parameters['gameId'], $user->parameters['playerId'], $armyId, $db);
                break;

            case 'disbandArmy':
                new Cli_DisbandArmy($dataIn['data']['armyId'], $user, $db, $this);
                break;

            case 'heroResurrection':
                new Cli_HeroResurrection($dataIn['data']['castleId'], $user, $db, $this);
                break;

            case 'ruin':
                new Cli_SearchRuin($dataIn['data']['armyId'], $user, $db, $this);
                break;

            case 'nextTurn':
                $token = Cli_Turn::next($user->parameters['gameId'], $user->parameters['playerId'], $db);
                $token['type'] = $dataIn['type'];

                $this->sendToChannel($token, Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db));
                break;

            case 'startTurn':
                $token = Cli_Turn::start($user->parameters['gameId'], $user->parameters['playerId'], $db);
                $token['type'] = $dataIn['type'];

                $this->sendToChannel($token, Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db));
                break;

            case 'razeCastle':
                new Cli_CastleRaze($dataIn['castleId'], $user, $db, $this);
                break;

            case 'castleBuildDefense':
                new Cli_CastleBuildDefense($dataIn['castleId'], $user, $db, $gameHandler);
                break;
        }
    }

    public function onDisconnect(IWebSocketConnection $user) {
        if (Zend_Validate::is($user->parameters['gameId'], 'Digits') || Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $db = Cli_Database::getDb();
            Cli_Database::updatePlayerInGameWSSUId($user->parameters['gameId'], $user->parameters['playerId'], null, $db);
//            Game_Cli_Database::disconnectFromGame($user->parameters['gameId'], $user->parameters['playerId'], $db);
//            $this->update($user->parameters['gameId'], $db);
        }

//        $this->say("[DEMO] {$user->getId()} disconnected");
    }

}
