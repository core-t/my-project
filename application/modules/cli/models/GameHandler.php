<?php

/**
 * This resource handler will respond to all messages sent to /game on the socketserver below
 *
 * All this handler does is receiving data from browsers and sending the responds back
 * @author Bartosz Krzeszewski
 *
 */
class Cli_Model_GameHandler extends Cli_Model_WofHandler
{

    public function __construct()
    {
        parent::__construct();
        $db = Cli_Model_Database::getDb();
        Zend_Registry::set('units', Cli_Model_Database::getUnits($db));
    }

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg)
    {

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
            $this->sendError($user, 'No "gameId" or "playerId". No authorized.');
            return;
        }


        if ($dataIn['type'] == 'chat') {
            new Cli_Model_Chat($dataIn['msg'], $user, $db, $this);
            return;
        }

        Cli_Model_Database::addGameHistoryIn($db, $user->parameters['gameId'], $user->parameters['playerId'], $msg->getData());

        if ($dataIn['type'] == 'computer') {
            new Cli_Model_Computer($user, $db, $this);
            return;
        }

        if (!Cli_Model_Database::isPlayerTurn($user->parameters['gameId'], $user->parameters['playerId'], $db)) {
            $this->sendError($user, 'Not your turn.');
            return;
        }

        switch ($dataIn['type']) {
            case 'move':
                if (!isset($dataIn['armyId'])) {
                    $this->sendError($user, 'No "armyId"!');
                    return;
                }

                if (!isset($dataIn['x'])) {
                    $this->sendError($user, 'No "x"!');
                    return;
                }

                if (!isset($dataIn['y'])) {
                    $this->sendError($user, 'No "y"!');
                    return;
                }

                new Cli_Model_Move($dataIn['armyId'], $dataIn['x'], $dataIn['y'], $user, $db, $this);
                break;

            case 'tower':
                $towerId = $dataIn['towerId'];
                if ($towerId === null) {
                    $this->sendError($user, 'No "towerId"!');
                    return;
                }

                if (Cli_Model_Database::towerExists($db, $towerId, $user->parameters['gameId'])) {
                    Cli_Model_Database::changeTowerOwner($db, $towerId, $user->parameters['playerId'], $user->parameters['gameId']);
                } else {
                    Cli_Model_Database::addTower($db, $towerId, $user->parameters['playerId'], $user->parameters['gameId']);
                }
                break;

            case 'splitArmy':
                new Cli_Model_SplitArmy($dataIn['armyId'], $dataIn['s'], $dataIn['h'], $user, $db, $this);
                break;

            case 'joinArmy':
                new Cli_Model_JoinArmy($dataIn['armyId'], $user, $db, $this);
                break;

            case 'fortifyArmy':
                $armyId = $dataIn['armyId'];
                if (empty($armyId)) {
                    $this->sendError($user, 'No "armyId"!');
                    return;
                }

                Cli_Model_Database::fortifyArmy($user->parameters['gameId'], $user->parameters['playerId'], $armyId, $db);
                break;

            case 'disbandArmy':
                new Cli_Model_DisbandArmy($dataIn['armyId'], $user, $db, $this);
                break;

            case 'heroResurrection':
                new Cli_Model_HeroResurrection($dataIn['castleId'], $user, $db, $this);
                break;

            case 'ruin':
                new Cli_Model_SearchRuin($dataIn['armyId'], $user, $db, $this);
                break;

            case 'nextTurn':
                $token = Cli_Model_Turn::next($user->parameters['gameId'], $user->parameters['playerId'], $db);
                $token['type'] = $dataIn['type'];

                $this->sendToChannel($db, $token, $user->parameters['gameId']);
                break;

            case 'startTurn':
                $token = Cli_Model_Turn::start($user->parameters['gameId'], $user->parameters['playerId'], $db);
                $token['type'] = $dataIn['type'];

                $this->sendToChannel($db, $token, $user->parameters['gameId']);
                break;

            case 'production':
                $castleId = $dataIn['castleId'];
                $unitId = $dataIn['unitId'];

                if ($castleId === null) {
                    $this->sendError($user, 'No "castleId"!');
                    return;
                }
                if (empty($unitId)) {
                    $this->sendError($user, 'No "unitId"!');
                    return;
                }

                if ($unitId == -1) {
                    $unitId = null;
                }

                if (!Cli_Model_Database::isPlayerCastle($user->parameters['gameId'], $castleId, $user->parameters['playerId'], $db)) {
                    $this->sendError($user, 'To nie jest Twój zamek!');
                    return;
                }

                if (Cli_Model_Database::setCastleProduction($user->parameters['gameId'], $castleId, $unitId, $user->parameters['playerId'], $db)) {
                    $token = array(
                        'type' => $dataIn['type'],
                        'unitId' => $unitId,
                        'castleId' => $castleId
                    );

                    $this->sendToChannel($db, $token, $user->parameters['gameId']);
                }
                break;

            case 'razeCastle':
                new Cli_Model_CastleRaze($dataIn['castleId'], $user, $db, $this);
                break;

            case 'castleBuildDefense':
                new Cli_Model_CastleBuildDefense($dataIn['castleId'], $user, $db, $this);
                break;
        }
    }

    public function onDisconnect(IWebSocketConnection $user)
    {
        if (Zend_Validate::is($user->parameters['gameId'], 'Digits') || Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $db = Cli_Model_Database::getDb();
            Cli_Model_Database::updatePlayerInGameWSSUId($user->parameters['gameId'], $user->parameters['playerId'], null, $db);
//            Game_Cli_Database::disconnectFromGame($user->parameters['gameId'], $user->parameters['playerId'], $db);
//            $this->update($user->parameters['gameId'], $db);
        }

//        $this->say("[DEMO] {$user->getId()} disconnected");
    }

    public function sendToChannel($db, $token, $gameId, $debug = null)
    {

        parent::sendToChannel($db, $token, $gameId, $debug);

        if (!Zend_Validate::is($gameId, 'Digits')) {
            return;
        }

        if ($token['type'] == 'chat') {
            return;
        }

        Cli_Model_Database::addGameHistoryOut($db, $gameId, $token);
    }
}
