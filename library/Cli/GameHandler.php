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
            $token = array(
                'type' => 'open'
            );

            $this->send($user, Zend_Json::encode($token));
            return;
        }

        if (!Zend_Validate::is($user->parameters['gameId'], 'Digits') || !Zend_Validate::is($user->parameters['playerId'], 'Digits')) {
            $this->sendError($user, 'Brak "gameId" lub "playerId". Brak autoryzacji.');
            return;
        }


        if ($dataIn['type'] == 'chat') {
            $token = array(
                'type' => $dataIn['type'],
                'msg' => $dataIn['data'],
                'color' => Cli_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db)
            );

            $this->sendToChannel($token, Cli_Database::getInGameWSSUIdsExceptMine($user->parameters['gameId'], $user->parameters['playerId'], $db));
            return;
        }

        if ($dataIn['type'] == 'computer') {
            if (!Cli_Database::isGameMaster($user->parameters['gameId'], $user->parameters['playerId'], $db)) {
                $this->sendError($user, 'Nie Twoja gra!');
                return;
            }
            $playerId = Cli_Database::getTurnPlayerId($user->parameters['gameId'], $db);
            if (!Cli_Database::isComputer($playerId, $db)) {
                $this->sendError($user, 'To nie komputer!');
                return;
            }

            if (!Cli_Database::playerTurnActive($user->parameters['gameId'], $playerId, $db)) {
                $token = Cli_ComputerMainBlocks::startTurn($user->parameters['gameId'], $playerId, $db);
            } else {
                $army = Cli_Database::getComputerArmyToMove($user->parameters['gameId'], $playerId, $db);
                if (!empty($army['armyId'])) {
                    $token = Cli_ComputerMainBlocks::moveArmy($user->parameters['gameId'], $playerId, $army, $db);
                } else {
                    $token = Cli_Turn::next($user->parameters['gameId'], $playerId, $db);
                    $token['action'] = 'end';
                }
            }

            switch ($token['action'])
            {
                case 'continue':
                    $token['type'] = 'computer';
                    break;
                case 'start':
                    $token['type'] = 'computerStart';
                    break;
                case 'end':
                    $token['type'] = 'nextTurn';
                    break;
                case 'gameover':
                    $token['type'] = 'computerGameover';
                    break;
            }

            $this->sendToChannel($token, Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db));
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

                $users = Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'startTurn':
                $token = Cli_Turn::start($user->parameters['gameId'], $user->parameters['playerId'], $db);
                $token['type'] = $dataIn['type'];

                $this->sendToChannel($token, Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db));
                break;

            case 'razeCastle':
                $cId = $dataIn['data']['castleId'];
                if ($cId == null) {
                    $this->sendError($user, 'Brak "castleId"!');
                    return;
                }

                Cli_Database::razeCastle($user->parameters['gameId'], $cId, $user->parameters['playerId'], $db);
                $gold = Cli_Database::getPlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $db) + 1000;
                Cli_Database::updatePlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $gold, $db);
                $token = Cli_Database::getCastle($user->parameters['gameId'], $cId, $db);
                $token['color'] = Cli_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db);
                $token['gold'] = $gold;
                $token['type'] = 'castle';

                $this->sendToChannel($token, Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db));
                break;

            case 'castleBuildDefense':
                $cId = $dataIn['data']['castleId'];
                if ($cId == null) {
                    $this->sendError($user, 'Brak "castleId"!');
                    return;
                }

                if (!Cli_Database::isPlayerCastle($user->parameters['gameId'], $cId, $user->parameters['playerId'], $db)) {
                    $this->sendError($user, 'To nie jest Twój zamek.');
                    break;
                }
                $gold = Cli_Database::getPlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $db);
                $defenseModifier = Cli_Database::getCastleDefenseModifier($user->parameters['gameId'], $cId, $db);
                $defensePoints = Application_Model_Board::getCastleDefense($cId);
                $defense = $defenseModifier + $defensePoints;
                $costs = 0;
                for ($i = 1; $i <= $defense; $i++)
                {
                    $costs += $i * 100;
                }
                if ($gold < $costs) {
                    $this->sendError($user, 'Za mało złota!');
                    return;
                }
                Cli_Database::buildDefense($user->parameters['gameId'], $cId, $user->parameters['playerId'], $db);
                $token = Cli_Database::getCastle($user->parameters['gameId'], $cId, $db);
                $token['defensePoints'] = $defensePoints;
                $token['color'] = Cli_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db);
                $token['gold'] = $gold - $costs;
                $token['type'] = 'castle';
                Cli_Database::updatePlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $token['gold'], $db);

                $this->sendToChannel($token, Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db));
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
