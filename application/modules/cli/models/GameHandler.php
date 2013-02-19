<?php

/**
 * This resource handler will respond to all messages sent to /game on the socketserver below
 *
 * All this handler does is receiving data from browsers and sending the responds back
 * @author Bartosz Krzeszewski
 *
 */
class Cli_Model_GameHandler extends Cli_Model_WofHandler {

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {

        $dataIn = Zend_Json::decode($msg->getData());
        print_r('ZAPYTANIE ');
        print_r($dataIn);

        $db = Cli_Model_Database::getDb();

        if ($dataIn['type'] == 'open') {
            $open = new Cli_Model_Open($dataIn, $user, $db, $this);
            $user->parameters = $open->getParameters();
            return;
        }

        if (!Zend_Validate::is($user->parameters['gameId'], 'Digits') || !Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $this->sendError($user, 'Brak "gameId" lub "playerId". Brak autoryzacji.');
            return;
        }


        if ($dataIn['type'] == 'chat') {
            new Cli_Model_Chat($dataIn['msg'], $user, $db, $this);
            return;
        }

        if ($dataIn['type'] == 'computer') {
            new Cli_Model_Computer($user, $db, $this);
            return;
        }

        if (!Cli_Model_Database::isPlayerTurn($user->parameters['gameId'], $user->parameters['playerId'], $db)) {
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

                new Cli_Model_Move($dataIn['armyId'], $dataIn['x'], $dataIn['y'], $user, $db, $this);
                break;

            case 'splitArmy':
                new Cli_Model_SplitArmy($dataIn['data']['armyId'], $dataIn['data']['s'], $dataIn['data']['h'], $user, $db, $this);
                break;

            case 'joinArmy':
                new Cli_Model_JoinArmy($dataIn['data']['armyId'], $user, $db, $this);
                break;

            case 'fortifyArmy':
                $armyId = $dataIn['armyId'];
                if (empty($armyId)) {
                    $this->sendError($user, 'Brak "armyId"!');
                    return;
                }

                Cli_Model_Database::fortifyArmy($user->parameters['gameId'], $user->parameters['playerId'], $armyId, $db);
                break;

            case 'disbandArmy':
                new Cli_Model_DisbandArmy($dataIn['data']['armyId'], $user, $db, $this);
                break;

            case 'heroResurrection':
                new Cli_Model_HeroResurrection($dataIn['data']['castleId'], $user, $db, $this);
                break;

            case 'ruin':
                new Cli_Model_SearchRuin($dataIn['data']['armyId'], $user, $db, $this);
                break;

            case 'nextTurn':
                $token = Cli_Model_Turn::next($user->parameters['gameId'], $user->parameters['playerId'], $db);
                $token['type'] = $dataIn['type'];

                $this->sendToChannel($token, Cli_Model_Database::getInGameWSSUIds($user->parameters['gameId'], $db));
                break;

            case 'startTurn':
                $token = Cli_Model_Turn::start($user->parameters['gameId'], $user->parameters['playerId'], $db);
                $token['type'] = $dataIn['type'];

                $this->sendToChannel($token, Cli_Model_Database::getInGameWSSUIds($user->parameters['gameId'], $db));
                break;

            case 'razeCastle':
                new Cli_Model_CastleRaze($dataIn['castleId'], $user, $db, $this);
                break;

            case 'castleBuildDefense':
                new Cli_Model_CastleBuildDefense($dataIn['castleId'], $user, $db, $gameHandler);
                break;
        }
    }

    public function onDisconnect(IWebSocketConnection $user) {
        if (Zend_Validate::is($user->parameters['gameId'], 'Digits') || Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $db = Cli_Model_Database::getDb();
            Cli_Model_Database::updatePlayerInGameWSSUId($user->parameters['gameId'], $user->parameters['playerId'], null, $db);
//            Game_Cli_Database::disconnectFromGame($user->parameters['gameId'], $user->parameters['playerId'], $db);
//            $this->update($user->parameters['gameId'], $db);
        }

//        $this->say("[DEMO] {$user->getId()} disconnected");
    }

}
