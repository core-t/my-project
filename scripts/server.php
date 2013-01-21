<?php

date_default_timezone_set('Europe/Warsaw');
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(__DIR__ . '/../application'));
set_include_path('../library');
require_once 'Zend/Loader/Autoloader.php';
$loader = Zend_Loader_Autoloader::getInstance();
$loader->registerNamespace('Custom_');

defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'production');

// initialize Zend_Application
$application = new Zend_Application(
                APPLICATION_ENV,
                APPLICATION_PATH . '/configs/application.ini'
);

$config = new Zend_Config($application->getBootstrap()->getOptions());
Zend_Registry::set('config', $config);

declare(ticks = 1);

interface IWebSocketServerObserver {

    public function onConnect(IWebSocketConnection $user);

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg);

    public function onDisconnect(IWebSocketConnection $user);

    public function onAdminMessage(IWebSocketConnection $user, IWebSocketMessage $msg);
}

/**
 * This demo resource handler will respond to all messages sent to /echo/ on the socketserver below
 *
 * All this handler does is echoing the responds to the user
 * @author Chris
 *
 */
class WofHandler extends WebSocket_UriHandler {

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {

        $data = Zend_Json::decode($msg->getData());
        print_r($data);

        switch ($data['type'])
        {
            case 'move':
                $this->move($data);
                break;

            case 'chat':
                $this->chat($data);
                break;

            case 'army':
                $parentArmyId = $data['data']['armyId'];
                if (!empty($parentArmyId)) {
                    $db = Application_Model_Database::getDb();
                    $army = Application_Model_Database::getArmyById($data['gameId'], $parentArmyId, $db);
                    $army['color'] = Application_Model_Database::getPlayerColor($data['gameId'], $army['playerId'], $db);
                    $army['center'] = $data['data']['center'];
                    $token = array(
                        'type' => $data['type'],
                        'data' => $army,
                        'playerId' => $data['playerId'],
                        'color' => $data['color']
                    );

                    $users = Application_Model_Database::getInGameWSSUIdsExceptMine($data['gameId'], $data['playerId'], $db);

                    $this->sendToChannel($token, $users);
                } else {
                    echo('Brak "armyId"!');
                }
                break;

            case 'armies':
                $color = $data['data']['color'];
                if (!empty($color)) {
                    $db = Application_Model_Database::getDb();
                    $playerId = Application_Model_Database::getPlayerIdByColor($data['gameId'], $color, $db);
                    if (!empty($playerId)) {
                        $token = array(
                            'type' => $data['type'],
                            'data' => Application_Model_Database::getPlayerArmies($data['gameId'], $playerId),
                            'playerId' => $data['playerId'],
                            'color' => $data['color']
                        );

                        $users = Application_Model_Database::getInGameWSSUIdsExceptMine($data['gameId'], $data['playerId'], $db);

                        $this->sendToChannel($token, $users);
                    } else {
                        echo('Brak $playerId!');
                    }
                } else {
                    echo('Brak "color"!');
                }
                break;

            case 'splitArmy':
                $parentArmyId = $data['data']['armyId'];
                $s = $data['data']['s'];
                $h = $data['data']['h'];
                if (empty($parentArmyId) || (empty($h) && empty($s))) {
                    echo('Brak "armyId", "s" lub "h"!');
                }
                $db = Application_Model_Database::getDb();
                $childArmyId = Application_Model_Database::splitArmy($data['gameId'], $h, $s, $parentArmyId, $data['playerId'], $db);
                if (empty($childArmyId)) {
                    echo('Brak "childArmyId"');
                    return;
                }
                $token = array(
                    'type' => $data['type'],
                    'data' => array(
                        'parentArmyId' => $parentArmyId,
                        'childArmy' => Application_Model_Database::getArmyById($data['gameId'], $childArmyId, $db),
                    ),
                    'playerId' => $data['playerId'],
                    'color' => $data['color']
                );

                $users = Application_Model_Database::getInGameWSSUIds($data['gameId'], $db);

                $this->sendToChannel($token, $users);

                break;

            case 'joinArmy':
                $armyId1 = $data['data']['armyId1'];
                $armyId2 = $data['data']['armyId2'];
                if (empty($armyId1) || empty($armyId2)) {
                    echo('Brak "armyId1" i "armyId2"!');
                    return;
                }
                $position1 = Application_Model_Database::getArmyPositionByArmyId($data['gameId'], $armyId1, $data['playerId'], $db);
                $position2 = Application_Model_Database::getArmyPositionByArmyId($data['gameId'], $armyId2, $data['playerId'], $db);
                if (empty($position1['x']) || empty($position1['y']) || ($position1['x'] != $position2['x']) || ($position1['y'] != $position2['y'])) {
                    echo('Armie nie są na tej samej pozycji!');
                    return;
                }
                $armyId = Application_Model_Database::joinArmiesAtPosition($data['gameId'], $position1, $data['playerId'], $db);
                if(empty($armyId)){
                    echo('Brak "armyId"!');
                    return;
                }
                $token = array(
                    'type' => $data['type'],
                    'data' => array(
                        'army' => Application_Model_Database::getArmyById($data['gameId'], $armyId, $db),
                    ),
                    'playerId' => $data['playerId'],
                    'color' => $data['color']
                );

                $users = Application_Model_Database::getInGameWSSUIds($data['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'tower':

                break;

            case 'ruin':
                $parentArmyId = $data['data']['armyId'];
                if (!Zend_Validate::is($parentArmyId, 'Digits')) {
                    echo('Brak "armyId"!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                $heroId = Application_Model_Database::getHeroIdByArmyIdPlayerId($data['gameId'], $parentArmyId, $data['playerId'], $db);
                if (empty($heroId)) {
                    echo('Brak heroId. Tylko Hero może przeszukiwać ruiny!');
                    return;
                }
                $position = Application_Model_Database::getArmyPositionByArmyId($data['gameId'], $parentArmyId, $data['playerId'], $db);
                $ruinId = Application_Model_Board::confirmRuinPosition($position);
                if (!Zend_Validate::is($ruinId, 'Digits')) {
                    echo('Brak ruinId na pozycji');
                    return;
                }
                if (Application_Model_Database::ruinExists($data['gameId'], $ruinId, $db)) {
                    echo('Ruiny są już przeszukane. ' . $ruinId . ' ' . $parentArmyId);
                    return;
                }

                $find = Application_Model_Database::searchRuin($data['gameId'], $ruinId, $heroId, $parentArmyId, $data['playerId'], $db);

                if (Application_Model_Database::ruinExists($data['gameId'], $ruinId, $db)) {
                    $ruin = array(
                        'ruinId' => $ruinId,
                        'empty' => 1
                    );
                } else {
                    $ruin = array(
                        'ruinId' => $ruinId,
                        'empty' => 0
                    );
                }

                $token = array(
                    'type' => $data['type'],
                    'data' => array(
                        'army' => Application_Model_Database::getArmyById($data['gameId'], $parentArmyId, $db),
                        'ruin' => $ruin,
                        'find' => $find
                    ),
                    'playerId' => $data['playerId'],
                    'color' => $data['color']
                );

                $users = Application_Model_Database::getInGameWSSUIds($data['gameId'], $db);

                $this->sendToChannel($token, $users);

                break;

            case 'turn':
                $token = array(
                    'type' => $data['type'],
                    'data' => Application_Model_Turn::next($data['gameId'], $data['playerId'], $data['color']),
                    'playerId' => $data['playerId'],
                    'color' => $data['color']
                );

                $users = Application_Model_Database::getInGameWSSUIds($data['gameId']);

                $this->sendToChannel($token, $users);
                break;

            case 'fightNeutralCastle':
                $parentArmyId = $data['data']['armyId'];
                $x = $data['data']['x'];
                $y = $data['data']['y'];
                $castleId = $data['data']['castleId'];

                if (Zend_Validate::is($parentArmyId, 'Digits') && Zend_Validate::is($x, 'Digits') && Zend_Validate::is($y, 'Digits') && Zend_Validate::is($castleId, 'Digits')) {
                    $castle = Application_Model_Board::getCastle($castleId);
                    if (empty($castle)) {
                        echo('Brak zamku o podanym ID!');
                        return;
                    }
                    if (($x >= $castle['position']['x']) AND ($x < ($castle['position']['x'] + 2)) AND ($y >= $castle['position']['y']) AND ($y < ($castle['position']['y'] + 2))) {
                        $db = Application_Model_Database::getDb();
                        $army = Application_Model_Database::getArmyByArmyIdPlayerId($data['gameId'], $parentArmyId, $data['playerId'], $db);
                        if (empty($army)) {
                            echo('Brak armii o podanym ID!');
                            return;
                        }
                        $distance = $this->calculateArmiesDistance($x, $y, $army['x'], $army['y']);
                        if ($distance >= 2) {
                            echo('Wróg znajduje się za daleko aby można go było atakować (' . $distance . '>=2).');
                            return;
                        }
                        $movesSpend = 2;
                        if ($movesSpend > $army['movesLeft']) {
                            echo('Armia ma za mało ruchów do wykonania akcji(' . $movesSpend . '>' . $army['movesLeft'] . ').');
                            return;
                        }
                        $battle = new Game_Battle($army, null, $data['gameId']);
                        $battle->fight();
                        $battle->updateArmies($data['gameId'], $db);
                        $defender = $battle->getDefender();

                        if (empty($defender['soldiers'])) {
                            $res = Application_Model_Database::addCastle($data['gameId'], $castleId, $data['playerId'], $db);
                            if ($res == 1) {
                                $movesAndPosition = array(
                                    'x' => $x,
                                    'y' => $y,
                                    'movesSpend' => $movesSpend
                                );
                                $res = Application_Model_Database::updateArmyPosition($data['gameId'], $parentArmyId, $data['playerId'], $movesAndPosition, $db);
                                switch ($res)
                                {
                                    case 1:
                                        $attacker = Application_Model_Database::getArmyByArmyIdPlayerId($data['gameId'], $parentArmyId, $data['playerId'], $db);
                                        $attacker['victory'] = true;
                                        $attacker['battle'] = $battle->getResult();
                                        $attacker['castleId'] = $castleId;

                                        $token = array(
                                            'type' => $data['type'],
                                            'playerId' => $data['playerId'],
                                            'color' => $data['color'],
                                            'data' => $attacker
                                        );

                                        $users = Application_Model_Database::getInGameWSSUIds($data['gameId'], $db);

                                        $this->sendToChannel($token, $users);
                                        break;
                                    case 0:
                                        echo('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                                        break;
                                    case null:
                                        echo('Zapytanie zwróciło błąd');
                                        break;
                                    default:
                                        echo('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                                        break;
                                }
                            } else {
                                echo('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.' . $res);
                            }
                        } else {
                            Application_Model_Database::destroyArmy($data['gameId'], $army['armyId'], $data['playerId'], $db);
                            $defender['battle'] = $battle->getResult();
                            $defender['victory'] = false;
                            $defender['castleId'] = $castleId;

                            $token = array(
                                'type' => $data['type'],
                                'playerId' => $data['playerId'],
                                'color' => $data['color'],
                                'data' => $defender
                            );

                            $users = Application_Model_Database::getInGameWSSUIds($data['gameId'], $db);

                            $this->sendToChannel($token, $users);
                        }
                    } else {
                        echo('Na podanej pozycji nie ma zamku!');
                    }
                } else {
                    echo('Brak "armyId" lub "x" lub "y"!');
                }
                break;

            case 'fightEnemyCastle':
                $parentArmyId = $data['data']['armyId'];
                $x = $data['data']['x'];
                $y = $data['data']['y'];
                $castleId = $data['data']['castleId'];
                if ($parentArmyId === null || $x === null || $y === null || $castleId === null) {
                    echo('Brak "armyId" lub "x" lub "y"!');
                    return;
                }
                $castle = Application_Model_Board::getCastle($castleId);
                if (empty($castle)) {
                    echo('Brak zamku o podanym ID!');
                    return;
                }
                if (($x < $castle['position']['x']) || ($x >= ($castle['position']['x'] + 2)) || ($y < $castle['position']['y']) || ($y >= ($castle['position']['y'] + 2))) {
                    echo('Na podanej pozycji nie ma zamku!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                $army = Application_Model_Database::getArmyByArmyIdPlayerId($data['gameId'], $parentArmyId, $data['playerId'], $db);
                if (empty($army)) {
                    echo('Brak armii o podanym ID!');
                    return;
                }
                $distance = $this->calculateArmiesDistance($x, $y, $army['x'], $army['y']);
                if ($distance >= 2) {
                    echo('Wróg znajduje się za daleko aby można go było atakować (' . $distance . '>=2).');
                    return;
                }
                $movesSpend = 2;
                if ($movesSpend > $army['movesLeft']) {
                    echo('Armia ma za mało ruchów do wykonania akcji(' . $movesSpend . '>' . $army['movesLeft'] . ').');
                    return;
                }
                if (!Application_Model_Database::isEnemyCastle($data['gameId'], $castleId, $data['playerId'], $db)) {
                    echo('To nie jest zamek wroga.');
                    return;
                }
                $battle = new Game_Battle($army, Application_Model_Database::getAllUnitsFromCastlePosition($data['gameId'], $castle['position'], $db), $data['gameId']);
                $battle->addCastleDefenseModifier($data['gameId'], $castleId, $db);
                $battle->fight();
                $battle->updateArmies($data['gameId'], $db);
                $defender = Application_Model_Database::updateAllArmiesFromCastlePosition($data['gameId'], $castle['position'], $db);
                if (empty($defender)) {
                    $changeOwnerResult = Application_Model_Database::changeOwner($data['gameId'], $castleId, $data['playerId'], $db);
                    if ($changeOwnerResult != 1) {
                        echo('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord. ' . $changeOwnerResult);
                        return;
                    }
                    $movesAndPosition = array(
                        'x' => $x,
                        'y' => $y,
                        'movesSpend' => $movesSpend
                    );
                    $updateArmyPositionResult = Application_Model_Database::updateArmyPosition($data['gameId'], $parentArmyId, $data['playerId'], $movesAndPosition, $db);
                    switch ($updateArmyPositionResult)
                    {
                        case 1:
                            $attacker = Application_Model_Database::getArmyByArmyIdPlayerId($data['gameId'], $parentArmyId, $data['playerId'], $db);
                            $attacker['victory'] = true;
                            $attacker['battle'] = $battle->getResult();
                            $attacker['castleId'] = $castleId;

                            $token = array(
                                'type' => $data['type'],
                                'playerId' => $data['playerId'],
                                'color' => $data['color'],
                                'data' => $attacker
                            );

                            $users = Application_Model_Database::getInGameWSSUIds($data['gameId'], $db);

                            $this->sendToChannel($token, $users);

                            break;
                        case 0:
                            echo('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                            break;
                        case null:
                            echo('Zapytanie zwróciło błąd');
                            break;
                        default:
                            echo('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                            echo $res;
                            break;
                    }
                } else {
                    Application_Model_Database::destroyArmy($data['gameId'], $army['armyId'], $data['playerId'], $db);
                    $defender['victory'] = false;
                    $defender['battle'] = $battle->getResult();
                    $defender['castleId'] = $castleId;

                    $token = array(
                        'type' => $data['type'],
                        'playerId' => $data['playerId'],
                        'color' => $data['color'],
                        'data' => $defender
                    );

                    $users = Application_Model_Database::getInGameWSSUIds($data['gameId'], $db);

                    $this->sendToChannel($token, $users);
                }

                break;

            case 'fightEnemy':
                $parentArmyId = $data['data']['armyId'];
                $x = $data['data']['x'];
                $y = $data['data']['y'];
                $enemyId = $data['data']['enemyArmyId'];
                if ($parentArmyId === null || $x === null || $y === null || $enemyId === null) {
                    echo('Brak "armyId" lub "x" lub "y" lub "$enemyId"!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                $army = Application_Model_Database::getArmyByArmyIdPlayerId($data['gameId'], $parentArmyId, $data['playerId'], $db);
                $distance = $this->calculateArmiesDistance($x, $y, $army['x'], $army['y']);
                if ($distance >= 2) {
                    echo('Wróg znajduje się za daleko aby można go było atakować (' . $distance . '>=2).');
                    return;
                }
                $movesSpend = $this->movesSpend($x, $y, $army);
                if ($movesSpend > $army['movesLeft']) {
                    echo('Armia ma za mało ruchów do wykonania akcji (' . $movesSpend . '>' . $army['movesLeft'] . ').');
                    return;
                }
                $enemy = Application_Model_Database::getAllUnitsFromPosition($data['gameId'], array('x' => $x, 'y' => $y), $db);
                $battle = new Game_Battle($army, $enemy, $data['gameId']);
                $battle->addTowerDefenseModifier($x, $y);
                $battle->fight();
                $battle->updateArmies($data['gameId'], $db);
                $defender = Application_Model_Database::updateAllArmiesFromPosition($data['gameId'], array('x' => $x, 'y' => $y), $db);
                if (empty($defender)) {
                    $movesAndPosition = array(
                        'x' => $x,
                        'y' => $y,
                        'movesSpend' => $movesSpend
                    );
                    $updateArmyPositionResult = Application_Model_Database::updateArmyPosition($data['gameId'], $parentArmyId, $data['playerId'], $movesAndPosition, $db);
                    switch ($updateArmyPositionResult)
                    {
                        case 1:
                            $attacker = Application_Model_Database::getArmyByArmyIdPlayerId($data['gameId'], $parentArmyId, $data['playerId'], $db);

                            $token = array(
                                'type' => $data['type'],
                                'playerId' => $data['playerId'],
                                'color' => $data['color'],
                                'data' => array(
                                    'army' => $attacker,
                                    'enemyArmy' => null,
                                    'battle' => $battle->getResult(),
                                    'victory' => true
                                )
                            );

                            $users = Application_Model_Database::getInGameWSSUIds($data['gameId'], $db);

                            $this->sendToChannel($token, $users);
                            break;
                        case 0:
                            echo('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                            break;
                        case null:
                            echo('Zapytanie zwróciło błąd');
                            break;
                        default:
                            echo('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                            break;
                    }
                } else {
                    Application_Model_Database::destroyArmy($data['gameId'], $army['armyId'], $data['playerId'], $db);

                    $token = array(
                        'type' => $data['type'],
                        'playerId' => $data['playerId'],
                        'color' => $data['color'],
                        'data' => array(
                            'army' => null,
                            'enemyArmy' => $defender,
                            'battle' => $battle->getResult(),
                            'victory' => false,
                        )
                    );

                    $users = Application_Model_Database::getInGameWSSUIds($data['gameId'], $db);

                    $this->sendToChannel($token, $users);
                }

                break;

            case 'razeCastle':
                $castleId = $data['data']['castleId'];
                if ($castleId == null) {
                    echo('Brak "castleId"!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                $razeCastleResult = Application_Model_Database::razeCastle($data['gameId'], $castleId, $data['playerId'], $db);
                switch ($razeCastleResult)
                {
                    case 1:
                        $gold = Application_Model_Database::getPlayerInGameGold($data['gameId'], $data['playerId'], $db) + 1000;
                        Application_Model_Database::updatePlayerInGameGold($data['gameId'], $data['playerId'], $gold, $db);
                        $response = Application_Model_Database::getCastle($data['gameId'], $castleId, $db);
                        $response['color'] = $data['color'];
                        $response['gold'] = $gold;
                        $token = array(
                            'type' => 'castle',
                            'playerId' => $data['playerId'],
                            'color' => $data['color'],
                            'data' => $response
                        );

                        $users = Application_Model_Database::getInGameWSSUIds($data['gameId'], $db);

                        $this->sendToChannel($token, $users);
                        break;
                    case 0:
                        echo('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                        break;
                    case null:
                        echo('Zapytanie zwróciło błąd');
                        break;
                    default:
                        echo('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                        break;
                }
                break;

            case 'castleBuildDefense':
                $castleId = $data['data']['castleId'];
                if ($castleId == null) {
                    echo('Brak "castleId"!');
                    return;
                }
                $db = Application_Model_Database::getDb();
                if (!Application_Model_Database::isPlayerCastle($data['gameId'], $castleId, $data['playerId'], $db)) {
                    echo('Nie Twój zamek.');
                    break;
                }
                $gold = Application_Model_Database::getPlayerInGameGold($data['gameId'], $data['playerId'], $db);
                $defenseModifier = Application_Model_Database::getCastleDefenseModifier($data['gameId'], $castleId, $db);
                $defensePoints = Application_Model_Board::getCastleDefense($castleId);
                $defense = $defenseModifier + $defensePoints;
                $costs = 0;
                for ($i = 1; $i <= $defense; $i++)
                {
                    $costs += $i * 100;
                }
                if ($gold < $costs) {
                    echo('Za mało złota!');
                    return;
                }
                $buildDefenseResult = Application_Model_Database::buildDefense($data['gameId'], $castleId, $data['playerId'], $db);
                switch ($buildDefenseResult)
                {
                    case 1:
                        $response = Application_Model_Database::getCastle($data['gameId'], $castleId, $db);
                        $response['defensePoints'] = $defensePoints;
                        $response['color'] = $data['color'];
                        $response['gold'] = $gold - $costs;
                        Application_Model_Database::updatePlayerInGameGold($data['gameId'], $data['playerId'], $response['gold'], $db);

                        $token = array(
                            'type' => 'castle',
                            'playerId' => $data['playerId'],
                            'color' => $data['color'],
                            'data' => $response
                        );

                        $users = Application_Model_Database::getInGameWSSUIds($data['gameId'], $db);

                        $this->sendToChannel($token, $users);
                        break;
                    case 0:
                        echo('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                        break;
                    case null:
                        echo('Zapytanie zwróciło błąd');
                        break;
                    default:
                        echo('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                        break;
                }
                break;

            case 'open':
                $this->open($user);
                break;
        }
    }

    public function sendToChannel($token, $users) {
        print_r($token);
        foreach ($users AS $row)
        {
            foreach ($this->users AS $u)
            {
                if ($u->getId() == $row['webSocketServerUserId']) {
                    $this->send($u, Zend_Json::encode($token));
                }
            }
        }
    }

    public function open($user) {
        $token = array(
            'type' => 'open',
            'wssuid' => $user->getId()
        );
        $re = new WebSocket_Message();

        $re->setData(Zend_Json::encode($token));
        $user->sendMessage($re);
    }

    private function chat($data) {
        $token = array(
            'type' => $data['type'],
            'msg' => $data['data'],
            'playerId' => $data['playerId'],
            'color' => $data['color']
        );

        $users = Application_Model_Database::getInGameWSSUIdsExceptMine($data['gameId'], $data['playerId']);

        $this->sendToChannel($token, $users);
    }

    private function move($data) {
        $db = Application_Model_Database::getDb();
        if (!Application_Model_Database::isPlayerTurn($data['gameId'], $data['playerId'], $db)) {
            echo('Nie Twoja tura.');
            return;
        }
        if (isset($data['data']['armyId'])) {
            $armyId = $data['data']['armyId'];
        }
        if (isset($data['data']['x'])) {
            $x = $data['data']['x'];
        }
        if (isset($data['data']['y'])) {
            $y = $data['data']['y'];
        }
        if (!empty($armyId) AND $x !== null AND $y !== null) {

            $mMove = new Application_Model_Move();
            $token = array(
                'type' => $data['type'],
                'data' => $mMove->go($data['gameId'], $armyId, $x, $y, $data['playerId']),
                'playerId' => $data['playerId'],
                'color' => $data['color']
            );

            $users = Application_Model_Database::getInGameWSSUIds($data['gameId'], $db);

            $this->sendToChannel($token, $users);
        } else {
            echo('Brak parametrów armii.');
            return;
        }
    }

    private function calculateArmiesDistance($x, $y, $x2, $y2) {
        return sqrt(pow($x - $x2, 2) + pow($y2 - $y, 2));
    }

    private function movesSpend($x, $y, $army) {
        $canFly = 1;
        $canSwim = 0;
        $movesRequiredToAttack = 1;
        $canFly -= count($army['heroes']);
//        foreach ($army['heroes'] as $hero) {
//            $canFly--;
//        }
        foreach ($army['soldiers'] as $soldier)
        {
            if ($soldier['canFly']) {
                $canFly++;
            } else {
                $canFly -= 200;
            }
            if ($soldier['canSwim']) {
                $canSwim++;
            }
        }
        $fields = Application_Model_Board::getBoardFields();
        $terrainType = $fields[$y][$x];
        $terrain = Application_Model_Board::getTerrain($terrainType, $canFly, $canSwim);
        return $terrain[1] + $movesRequiredToAttack;
    }

}

/**
 * Demo socket server. Implements the basic eventlisteners and attaches a resource handler for /echo/ urls.
 *
 *
 * @author Chris
 *
 */
class WofSocketServer implements IWebSocketServerObserver {

    protected $debug = true;
    protected $server;

    public function __construct() {
        $this->server = new WebSocket_Server('tcp://' . Zend_Registry::get('config')->websockets->aHost . ':' . Zend_Registry::get('config')->websockets->aPort, 'superdupersecretkey');
        $this->server->addObserver($this);

        $this->server->addUriHandler('wof', new WofHandler());
    }

    public function onConnect(IWebSocketConnection $user) {
        $this->say("[DEMO] {$user->getId()} connected");
    }

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {
        $this->say("[DEMO] {$user->getId()} says '{$msg->getData()}'");
    }

    public function onDisconnect(IWebSocketConnection $user) {
        $this->say("[DEMO] {$user->getId()} disconnected");
    }

    public function onAdminMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {
        $this->say("[DEMO] Admin Message received!");

        $frame = WebSocketFrame::create(WebSocketOpcode::PongFrame);
        $user->sendFrame($frame);
    }

    public function say($msg) {
        echo "$msg \r\n";
    }

    public function run() {
        $this->server->run();
    }

}

// Start server
$server = new WofSocketServer();
$server->run();

exit;
