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
                $armyId = $data['data']['armyId'];
                if (!empty($armyId)) {
                    $db = Application_Model_Database::getDb();
                    $army = Application_Model_Database::getArmyById($data['gameId'], $armyId, $db);
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
                    $playerId = Application_Model_Database::getPlayerIdByColor($data['gameId'], $color);
                    if (!empty($playerId)) {
                        $token = array(
                            'type' => $data['type'],
                            'data' => Application_Model_Database::getPlayerArmies($data['gameId'], $playerId),
                            'playerId' => $data['playerId'],
                            'color' => $data['color']
                        );

                        $users = Application_Model_Database::getInGameWSSUIdsExceptMine($data['gameId'], $data['playerId']);

                        $this->sendToChannel($token, $users);
                    } else {
                        echo('Brak $playerId!');
                    }
                } else {
                    echo('Brak "color"!');
                }
                break;

            case 'ruin':
                $ruinId = $data['data']['ruinId'];
                if ($ruinId !== null) {
                    $db = Application_Model_Database::getDb();
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
                        'data' => $ruin,
                        'playerId' => $data['playerId'],
                        'color' => $data['color']
                    );

                    $users = Application_Model_Database::getInGameWSSUIdsExceptMine($data['gameId'], $data['playerId'], $db);

                    $this->sendToChannel($token, $users);
                } else {
                    echo('Brak "ruinId"!');
                }
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
                $armyId = $data['data']['armyId'];
                $x = $data['data']['x'];
                $y = $data['data']['y'];
                $castleId = $data['data']['castleId'];

                if (Zend_Validate::is($armyId, 'Digits') && Zend_Validate::is($x, 'Digits') && Zend_Validate::is($y, 'Digits') && Zend_Validate::is($castleId, 'Digits')) {
                    $castle = Application_Model_Board::getCastle($castleId);
                    if (empty($castle)) {
                        echo('Brak zamku o podanym ID!');
                        return;
                    }
                    if (($x >= $castle['position']['x']) AND ($x < ($castle['position']['x'] + 2)) AND ($y >= $castle['position']['y']) AND ($y < ($castle['position']['y'] + 2))) {
                        $db = Application_Model_Database::getDb();
                        $army = Application_Model_Database::getArmyByArmyIdPlayerId($data['gameId'], $armyId, $data['playerId'], $db);
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
                                $res = Application_Model_Database::updateArmyPosition($data['gameId'], $armyId, $data['playerId'], $movesAndPosition, $db);
                                switch ($res)
                                {
                                    case 1:
                                        $attacker = Application_Model_Database::getArmyByArmyIdPlayerId($data['gameId'], $armyId, $data['playerId'], $db);
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
                $armyId = $data['data']['armyId'];
                $x = $data['data']['x'];
                $y = $data['data']['y'];
                $castleId = $data['data']['castleId'];
                if ($armyId === null || $x === null || $y === null || $castleId === null) {
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
                $army = Application_Model_Database::getArmyByArmyIdPlayerId($data['gameId'], $armyId, $data['playerId'], $db);
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
                    $updateArmyPositionResult = Application_Model_Database::updateArmyPosition($data['gameId'], $armyId, $data['playerId'], $movesAndPosition, $db);
                    switch ($updateArmyPositionResult)
                    {
                        case 1:
                            $attacker = Application_Model_Database::getArmyByArmyIdPlayerId($data['gameId'], $armyId, $data['playerId'], $db);
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

            case 'open':
                $this->open($user);
                break;
        }
    }

    public function sendToChannel($token, $users) {

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
        if (!Application_Model_Database::isPlayerTurn($data['gameId'], $data['playerId'])) {
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

            $users = Application_Model_Database::getInGameWSSUIds($data['gameId']);

            $this->sendToChannel($token, $users);
        } else {
            echo('Brak parametrów armii.');
            return;
        }
    }

    private function calculateArmiesDistance($x, $y, $x2, $y2) {
        return sqrt(pow($x - $x2, 2) + pow($y2 - $y, 2));
    }

}

/**
 * Demo socket server. Implements the basic eventlisteners and attaches a resource handler for /echo/ urls.
 *
 *
 * @author Chris
 *
 */
class DemoSocketServer implements IWebSocketServerObserver {

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



//        $this->say("[DEMO] {$user->getId()} says '{$msg->getData()}'");
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
$server = new DemoSocketServer();
$server->run();

exit;
