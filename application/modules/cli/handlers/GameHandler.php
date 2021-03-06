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
            Zend_Registry::set('terrain', $mMapTerrain->getTerrain());
            $units = $mMapUnits->getUnits();
            Zend_Registry::set('units', $units);
            $specialUnits = array();
            foreach ($units as $unit) {
                if ($unit['special']) {
                    $specialUnits[] = $unit;
                }
            }
            Zend_Registry::set('specialUnits', $specialUnits);
            reset($units);
            Zend_Registry::set('firstUnitId', key($units));
            Zend_Registry::set('fields', $mMapFields->getMapFields());
            $castles = $mMapCastles->getMapCastles();
            $mCastleProduction = new Application_Model_CastleProduction($db);
            foreach (array_keys($castles) as $castleId) {
                $castles[$castleId]['production'] = $mCastleProduction->getCastleProduction($castleId);
            }
            Zend_Registry::set('castles', $castles);
            Zend_Registry::set('ruins', $mMapRuins->getMapRuins());
            Zend_Registry::set('towers', $mMapTowers->getMapTowers());
            Zend_Registry::set('playersInGameColors', $mPlayersInGame->getAllColors());
            Zend_Registry::set('capitals', $mMapPlayers->getCapitals());

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
                new Cli_Model_Move($dataIn, $user, $db, $this);
                break;

            case 'split':
                $mSplitArmy = new Cli_Model_SplitArmy();
                $mSplitArmy->split($dataIn['armyId'], $dataIn['s'], $dataIn['h'], $user, $user->parameters['playerId'], $db, $this);
                break;

            case 'join':
                new Cli_Model_JoinArmy($dataIn['armyId'], $user, $db, $this);
                break;

            case 'fortify':
                $armyId = $dataIn['armyId'];
                if (empty($armyId)) {
                    $this->sendError($user, 'No "armyId"!');
                    return;
                }

                $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);
                $mArmy2->fortify($armyId, $dataIn['fortify'], $user->parameters['playerId']);
                break;

            case 'disband':
                new Cli_Model_DisbandArmy($dataIn['armyId'], $user, $db, $this);
                break;

            case 'resurrection':
                new Cli_Model_HeroResurrection($user, $db, $this);
                break;

            case 'hire':
                new Cli_Model_HeroHire($user, $db, $this);
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
                new Cli_Model_Production($dataIn, $user, $db, $this);
                break;

            case 'raze':
                new Cli_Model_CastleRaze($dataIn['armyId'], $user, $db, $this);
                break;

            case 'defense':
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
