<?php

/**
 * This resource handler will respond to all messages sent to /game on the socketserver below
 *
 * All this handler does is receiving data from browsers and sending the responds back
 * @author Bartosz Krzeszewski
 *
 */
class Cli_Model_GameHandler extends Cli_WofHandler
{

    public function __construct()
    {
        parent::__construct();
    }

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg)
    {

        $dataIn = Zend_Json::decode($msg->getData());
        print_r('ZAPYTANIE ');
        print_r($dataIn);
        new Coret_Model_Logger($dataIn);

        $db = Cli_Model_Database::getDb();

        if ($dataIn['type'] == 'open') {
            $open = new Cli_Model_Open($dataIn, $user, $db, $this);
            $user->parameters = $open->getParameters();

            $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
            $mMapFields = new Application_Model_MapFields($mGame->getMapId(), $db);
            $mMapCastles = new Application_Model_MapCastles($mGame->getMapId(), $db);
            $mMapRuins = new Application_Model_MapRuins($mGame->getMapId(), $db);
            $mMapTowers = new Application_Model_MapTowers($mGame->getMapId(), $db);
            $mMapUnits = new Application_Model_MapUnits($mGame->getMapId(), $db);

            $units = $mMapUnits->getUnits($db);
            Zend_Registry::set('units', $units);
            Zend_Registry::set('fields', $mMapFields->getMapFields());
            Zend_Registry::set('castles', $mMapCastles->getMapCastles());
            Zend_Registry::set('ruins', $mMapRuins->getMapRuins());
            Zend_Registry::set('towers', $mMapTowers->getMapTowers());

            reset($units);
            next($units);
            Zend_Registry::set('fistUnitId', key($units));

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

        Cli_Model_Database::addTokensIn($db, $user->parameters['gameId'], $user->parameters['playerId'], $dataIn);

        if ($dataIn['type'] == 'computer') {
            new Cli_Model_Computer($user, $db, $this);
            return;
        }

        if ($dataIn['type'] == 'tower') {
            $towerId = $dataIn['towerId'];
            if ($towerId === null) {
                $this->sendError($user, 'No "towerId"!');
                return;
            }

            $playerId = Cli_Model_Database::getTurnPlayerId($user->parameters['gameId'], $db);
            // sprawdzić czy armia gracza jest w pobliżu wieży

            $mTowersInGame = new Application_Model_TowersInGame($user->parameters['gameId'], $db);
            if ($mTowersInGame->towerExists($towerId)) {
                $mTowersInGame->changeTowerOwner($towerId, $playerId);
            } else {
                $mTowersInGame->addTower($towerId, $playerId);
            }
            return;
        }

        if (!Cli_Model_Database::isPlayerTurn($user->parameters['gameId'], $user->parameters['playerId'], $db)) {
            $this->sendError($user, 'Not your turn.');

            if (Zend_Registry::get('config')->exitOnErrors) {
                exit;
            }
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


                if (!Cli_Model_Database::isPlayerCastle($user->parameters['gameId'], $castleId, $user->parameters['playerId'], $db)) {
                    $this->sendError($user, 'To nie jest Twój zamek!');
                    return;
                }

                if ($unitId != -1) {
                    $mMapCastlesProduction = new Application_Model_MapCastlesProduction($db);
                    $production = $mMapCastlesProduction->getCastleProduction($castleId);
                    if (!isset($production[$unitId])) {
                        $this->sendError($user, 'Can\'t produce this unit here!');
                        return;
                    }
                } else {
                    $unitId = null;
                }

                $mCastlesInGame = new Application_Model_CastlesInGame($user->parameters['gameId'], $db);

                if ($mCastlesInGame->setProduction($castleId, $user->parameters['playerId'], $unitId)) {
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
        new Coret_Model_Logger($token);
        parent::sendToChannel($db, $token, $gameId, $debug);

        if ($token['type'] == 'chat') {
            return;
        }

        Cli_Model_Database::addTokensOut($db, $gameId, $token);
    }
}
