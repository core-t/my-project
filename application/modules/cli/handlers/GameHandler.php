<?php

/**
 * This resource handler will respond to all messages sent to /game on the socketserver below
 *
 * All this handler does is receiving data from browsers and sending the responds back
 * @author Bartosz Krzeszewski
 *
 */
class Cli_GameHandler extends Cli_WofHandler
{

    public function __construct()
    {
        parent::__construct();
    }

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg)
    {

        $dataIn = Zend_Json::decode($msg->getData());

        if (Zend_Registry::get('config')->debug) {
            print_r('ZAPYTANIE ');
            print_r($dataIn);
        }

        $l = new Coret_Model_Logger();
        $l->log($dataIn);

        $db = Cli_Model_Database::getDb();

        if ($dataIn['type'] == 'open') {
            $open = new Cli_Model_Open($dataIn, $user, $db, $this);
            $user->parameters = $open->getParameters();

            $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
            $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);

            $mapId = $mGame->getMapId();

            $mMapFields = new Application_Model_MapFields($mapId, $db);
            $mMapCastles = new Application_Model_MapCastles($mapId, $db);
            $mMapRuins = new Application_Model_MapRuins($mapId, $db);
            $mMapTowers = new Application_Model_MapTowers($mapId, $db);
            $mMapUnits = new Application_Model_MapUnits($mapId, $db);
            $mMapPlayers = new Application_Model_MapPlayers($mapId, $db);
            $mMapTerrain = new Application_Model_MapTerrain($mapId, $db);

            Zend_Registry::set('id_lang', $user->parameters['langId']);
            $units = $mMapUnits->getUnits();
            Zend_Registry::set('terrain', $mMapTerrain->getTerrain());
            Zend_Registry::set('units', $units);
            Zend_Registry::set('fields', $mMapFields->getMapFields());
            Zend_Registry::set('castles', $mMapCastles->getMapCastles());
            Zend_Registry::set('ruins', $mMapRuins->getMapRuins());
            Zend_Registry::set('towers', $mMapTowers->getMapTowers());
            Zend_Registry::set('colors', $mMapPlayers->getColors());

            reset($units);
            next($units);
            Zend_Registry::set('fistUnitId', key($units));

            Zend_Registry::set('playersInGameColors', $mPlayersInGame->getAllColors());

            return;
        }

        if (!Zend_Validate::is($user->parameters['gameId'], 'Digits') || !Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $this->sendError($user, 'No "gameId" or "playerId". Not authorized.');
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

        if ($dataIn['type'] == 'statistics') {
            $playersInGameColors = Zend_Registry::get('playersInGameColors');

            $mCastlesConquered = new Application_Model_CastlesConquered($user->parameters['gameId'], $db);
            $mCastlesDestroyed = new Application_Model_CastlesDestroyed($user->parameters['gameId'], $db);
            $mHeroesKilled = new Application_Model_HeroesKilled($user->parameters['gameId'], $db);
            $mSoldiersKilled = new Application_Model_SoldiersKilled($user->parameters['gameId'], $db);
            $mSoldiersCreated = new Application_Model_SoldiersCreated($user->parameters['gameId'], $db);

            $token = array(
                'type' => $dataIn['type'],
                'castlesConquered' => array(
                    'winners' => $mCastlesConquered->countConquered($playersInGameColors),
                    'losers' => $mCastlesConquered->countLost($playersInGameColors)
                ),
                'heroesKilled' => array(
                    'winners' => $mHeroesKilled->countKilled($playersInGameColors),
                    'losers' => $mHeroesKilled->countLost($playersInGameColors)
                ),
                'soldiersKilled' => array(
                    'winners' => $mSoldiersKilled->countKilled($playersInGameColors),
                    'losers' => $mSoldiersKilled->countLost($playersInGameColors)
                ),
                'soldiersCreated' => $mSoldiersCreated->countCreated($playersInGameColors),
                'castlesDestroyed' => $mCastlesDestroyed->countAll($playersInGameColors)
            );

            $this->sendToChannel($db, $token, $user->parameters['gameId']);

            return;
        }

        if ($dataIn['type'] == 'tower') {
            $towerId = $dataIn['towerId'];
            if ($towerId === null) {
                $this->sendError($user, 'No "towerId"!');
                return;
            }

            $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
            $playerId = $mGame->getTurnPlayerId();
            // sprawdzić czy armia gracza jest w pobliżu wieży

            $mTowersInGame = new Application_Model_TowersInGame($user->parameters['gameId'], $db);
            if ($mTowersInGame->towerExists($towerId)) {
                $mTowersInGame->changeTowerOwner($towerId, $playerId);
            } else {
                $mTowersInGame->addTower($towerId, $playerId);
            }
            return;
        }

        if (!isset($mGame)) {
            $mGame = new Application_Model_Game($user->parameters['gameId'], $db);
        }

        if (!$mGame->isPlayerTurn($user->parameters['playerId'])) {
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

                $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);
                $mArmy2->fortify($armyId, $dataIn['fortify'], $user->parameters['playerId']);
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
                $mTurn = new Cli_Model_Turn($user->parameters['gameId'], $db, $this);
                $mTurn->next($user->parameters['playerId']);
                break;

            case 'startTurn':
                $mTurn = new Cli_Model_Turn($user->parameters['gameId'], $db, $this);
                $mTurn->start($user->parameters['playerId']);
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


                $mCastlesInGame = new Application_Model_CastlesInGame($user->parameters['gameId'], $db);
                if (!$mCastlesInGame->isPlayerCastle($castleId, $user->parameters['playerId'])) {
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
                new Cli_Model_CastleRaze($dataIn['armyId'], $user, $db, $this);
                break;

            case 'castleBuildDefense':
                new Cli_Model_CastleBuildDefense($dataIn['castleId'], $user, $db, $this);
                break;

            case 'inventoryAdd':
                new Cli_Model_InventoryAdd($dataIn['heroId'], $dataIn['artifactId'], $user, $db, $this);
                break;

            case 'inventoryDel':

                break;

            case 'surrender':
                new Cli_Model_Surrender($user, $db, $this);
                break;
        }
    }

    public function onDisconnect(IWebSocketConnection $user)
    {
        if (Zend_Validate::is($user->parameters['gameId'], 'Digits') || Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $db = Cli_Model_Database::getDb();

            $mPlayersInGame = new Application_Model_PlayersInGame($user->parameters['gameId'], $db);
            $mPlayersInGame->updatePlayerInGameWSSUId($user->parameters['playerId'], null);

//            Game_Cli_Database::disconnectFromGame($user->parameters['gameId'], $user->parameters['playerId'], $db);
//            $this->update($user->parameters['gameId'], $db);
        }

//        $this->say("[DEMO] {$user->getId()} disconnected");
    }

    public function sendToChannel($db, $token, $gameId, $debug = null)
    {
//        $l = new Coret_Model_Logger();
//        $l->log($token);
        parent::sendToChannel($db, $token, $gameId, $debug);

        if ($token['type'] == 'chat') {
            return;
        }

        Cli_Model_Database::addTokensOut($db, $gameId, $token);
    }
}
