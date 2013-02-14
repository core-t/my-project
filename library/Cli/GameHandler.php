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
//        print_r($user->parameters);
        $dataIn = Zend_Json::decode($msg->getData());
//        print_r('ZAPYTANIE ');
//        print_r($dataIn);

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
                if (isset($dataIn['data']['armyId'])) {
                    $attackerArmyId = $dataIn['data']['armyId'];
                }

                if (isset($dataIn['data']['x'])) {
                    $x = $dataIn['data']['x'];
                }

                if (isset($dataIn['data']['y'])) {
                    $y = $dataIn['data']['y'];
                }

                if (!Zend_Validate::is($attackerArmyId, 'Digits') || !Zend_Validate::is($x, 'Digits') || !Zend_Validate::is($y, 'Digits')) {
                    $this->sendError($user, 'Brak "armyId" lub "x" lub "y" lub "castleId"!');
                    return;
                }

                $defenderColor = null;
                $defender = null;
                $enemy = null;
                $attacker = null;
                $battleResult = null;
                $victory = false;
                $deletedIds = null;
                $castleId = null;
                $rollbackPath = null;

                $army = Cli_Database::getArmyByArmyIdPlayerId($user->parameters['gameId'], $attackerArmyId, $user->parameters['playerId'], $db);

                if (empty($army)) {
                    $this->sendError($user, 'Brak armii o podanym ID!');
                    return;
                }

                $canFly = -count($army['heroes']) + 1;
                $canSwim = 0;

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

                $fields = Cli_Database::getEnemyArmiesFieldsPositions($user->parameters['gameId'], $user->parameters['playerId'], $db);
                $castlesSchema = Application_Model_Board::getCastlesSchema();
                $allCastles = Cli_Database::getAllCastles($user->parameters['gameId'], $db);

                $aP = array(
                    'x' => $x,
                    'y' => $y
                );

                foreach ($castlesSchema as $cId => $castle)
                {
                    if (!isset($allCastles[$cId])) { // castle is neutral
                        if (Application_Model_Board::isCastleFild($aP, Application_Model_Board::getCastlePosition($cId))) { // trakuję neutralny zamek jak własny ponieważ go atakuję i jeśli wygram to będę mógł po nim chodzić
                            $fields = Application_Model_Board::changeCasteFields($fields, $castle['position']['x'], $castle['position']['y'], 'c');
                            $castleId = $cId;
                            $defenderColor = 'neutral';
                        } else {
                            $fields = Application_Model_Board::changeCasteFields($fields, $castle['position']['x'], $castle['position']['y'], 'e');
                        }
                        continue;
                    }

                    if ($allCastles[$cId]['razed']) { // castle is razed
                        continue;
                    }

                    if ($user->parameters['playerId'] == $allCastles[$cId]['playerId']) { // my castle
                        $fields = Application_Model_Board::changeCasteFields($fields, $castle['position']['x'], $castle['position']['y'], 'c');
                    } else { // enemy castle
                        if (Application_Model_Board::isCastleFild($aP, Application_Model_Board::getCastlePosition($cId))) { // trakuję zamek wroga jak własny ponieważ go atakuję i jeśli wygram to będę mógł po nim chodzić
                            $fields = Application_Model_Board::changeCasteFields($fields, $castle['position']['x'], $castle['position']['y'], 'c');
                            $castleId = $cId;
                        } else {
                            $fields = Application_Model_Board::changeCasteFields($fields, $castle['position']['x'], $castle['position']['y'], 'e');
                        }
                    }
                }

                if ($castleId === null) {
                    $enemy = Cli_Database::getAllEnemyUnitsFromPosition($user->parameters['gameId'], array('x' => $x, 'y' => $y), $user->parameters['playerId'], $db);
                    if ($enemy['ids']) { // enemy army
                        $fields = Application_Model_Board::changeArmyField($fields, $x, $y, 'c');
                    } else { // idziemy nie walczymy
                        if (Cli_Database::areMySwimmingUnitsAtPosition($user->parameters['gameId'], array('x' => $x, 'y' => $y), $user->parameters['playerId'], $db)) {
                            $fields = Application_Model_Board::changeArmyField($fields, $x, $y, 'b');
                        }
                    }
                }

                /*
                 * A* START
                 */

                $A_Star = new Cli_Astar($x, $y);

                try {
                    $A_Star->start($army['x'], $army['y'], $fields, $canFly, $canSwim);
                    $move = array(
                        'path' => $A_Star->getPath($x . '_' . $y, $army['movesLeft']),
                        'currentPosition' => $A_Star->getCurrentPosition(),
                    );
                } catch (Exception $e) {
                    echo($e);
                    $this->sendError($user, 'Wystąpił błąd podczas obliczania ścieżki');
                    return;
                }

                /*
                 * A* END
                 */

                if (!$move['currentPosition']) {
                    print_r($move);
                    $this->sendError($user, 'Za mało punktów ruchu aby wykonać akcję');
                    return;
                }

                if ($move['currentPosition']['movesSpend'] > $army['movesLeft']) {
                    $this->sendError($user, 'Próba wykonania większej ilości ruchów niż jednostka posiada');
                    return;
                }

                $fight = false;
                $movesLeft = $army['movesLeft'] - $move['currentPosition']['movesSpend'];

                if ($move['currentPosition']['x'] == $x && $move['currentPosition']['y'] == $y) {
                    if (Zend_Validate::is($castleId, 'Digits')) { // castle
                        if ($movesLeft >= 2) {
                            $fight = true;
                            if ($defenderColor == 'neutral') {
                                $enemy = Cli_Battle::getNeutralCastleGarrizon($user->parameters['gameId'], $db);
                            } else { // kolor wrogiego zamku sprawdzam dopiero wtedy gdy wiem, że armia ma na niego zasięg
                                $defenderColor = Cli_Database::getColorByCastleId($user->parameters['gameId'], $castleId, $db);
                                $enemy = Cli_Database::getAllUnitsFromCastlePosition($user->parameters['gameId'], Application_Model_Board::getCastlePosition($castleId), $db);
                            }
                        } else {
                            $rollbackPath = true;
                        }
                    } elseif ($enemy['ids']) { // enemy army
                        if ($movesLeft >= 2) {
                            $fight = true;
                            $defenderColor = Cli_Database::getColorByArmyId($user->parameters['gameId'], $enemy['ids'][0], $db);
                        } else {
                            $rollbackPath = true;
                        }
                    }
                }

                /* ------------------------------------
                 *
                 * ZMIANY ZAPISUJĘ PONIZEJ TEJ LINII
                 *
                 * ------------------------------------ */

                if ($fight) {
                    $battle = new Cli_Battle($army, $enemy);

                    if (Zend_Validate::is($castleId, 'Digits')) {
                        if ($defenderColor == 'neutral') {
                            $battle->fight();
                            $battle->updateArmies($user->parameters['gameId'], $db);
                            $defender = $battle->getDefender();
                        } else {
                            $battle->addCastleDefenseModifier($user->parameters['gameId'], $castleId, $db);
                            $battle->fight();
                            $battle->updateArmies($user->parameters['gameId'], $db);
                            $castle = Application_Model_Board::getCastle($castleId);
                            $defender = Cli_Database::updateAllArmiesFromCastlePosition($user->parameters['gameId'], $castle['position'], $db);
                        }
                    } else {
                        $battle->addTowerDefenseModifier($x, $y);
                        $battle->fight();
                        $battle->updateArmies($user->parameters['gameId'], $db);
                        $defender = Cli_Database::updateAllArmiesFromPosition($user->parameters['gameId'], array('x' => $x, 'y' => $y), $db);
                    }

                    if (empty($defender)) {
                        if (Zend_Validate::is($castleId, 'Digits')) {
                            if ($defenderColor == 'neutral') {
                                Cli_Database::addCastle($user->parameters['gameId'], $castleId, $user->parameters['playerId'], $db);
                            } else {
                                Cli_Database::changeOwner($user->parameters['gameId'], $castleId, $user->parameters['playerId'], $db);
                            }
                        }
                        $move['currentPosition']['movesSpend'] += 2;
                        Cli_Database::updateArmyPosition($user->parameters['gameId'], $attackerArmyId, $user->parameters['playerId'], $move['currentPosition'], $db);
                        $attacker = Cli_Database::getArmyByArmyIdPlayerId($user->parameters['gameId'], $attackerArmyId, $user->parameters['playerId'], $db);
                        $victory = true;
                        foreach ($enemy['ids'] as $id)
                        {
                            $defender[]['armyId'] = $id;
                        }
                    } else {
                        Cli_Database::destroyArmy($user->parameters['gameId'], $army['armyId'], $user->parameters['playerId'], $db);
                        $attacker = array(
                            'armyId' => $attackerArmyId,
                            'destroyed' => true
                        );
                        if ($defenderColor == 'neutral') {
                            $defender = null;
                        }
                    }
                    $battleResult = $battle->getResult($army, $enemy);
                } else {
                    if ($rollbackPath) {
                        if (Zend_Validate::is($castleId, 'Digits')) {
                            if (!$move['currentPosition']['movesSpend']) {
                                $this->sendError($user, 'Nie wykonano ruchu');
                                return;
                            }
                            $newMove = Application_Model_Board::rewindPathOutOfCastle($move['path'], $castleId);
                            $newMove['currentPosition']['movesSpend'] = $move['currentPosition']['movesSpend'];
                            $move = $newMove;
                        } else {
                            array_pop($move['path']);
                            if (!$move['currentPosition']['movesSpend']) {
                                $this->sendError($user, 'Nie wykonano ruchu');
                                return;
                            }
                            $count = count($move['path']);
                            $move['currentPosition']['x'] = $move['path'][$count]['x'];
                            $move['currentPosition']['y'] = $move['path'][$count]['y'];
                        }
                    }
                    Cli_Database::updateArmyPosition($user->parameters['gameId'], $attackerArmyId, $user->parameters['playerId'], $move['currentPosition'], $db);
                    $armiesIds = Cli_Database::joinArmiesAtPosition($user->parameters['gameId'], $move['currentPosition'], $user->parameters['playerId'], $db);
                    $newArmyId = $armiesIds['armyId'];
                    $attacker = Cli_Database::getArmyByArmyIdPlayerId($user->parameters['gameId'], $newArmyId, $user->parameters['playerId'], $db);
                    $deletedIds = $armiesIds['deletedIds'];
                }

                $token = array(
                    'type' => 'move',
                    'attackerColor' => Cli_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db),
                    'attackerArmy' => $attacker,
                    'defenderColor' => $defenderColor,
                    'defenderArmy' => $defender,
                    'battle' => $battleResult,
                    'victory' => $victory,
                    'x' => $x,
                    'y' => $y,
                    'castleId' => $castleId,
                    'path' => $move['path'],
                    'oldArmyId' => $attackerArmyId,
                    'deletedIds' => $deletedIds,
                );

                $users = Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'splitArmy':
                $attackerArmyId = $dataIn['data']['armyId'];
                $s = $dataIn['data']['s'];
                $h = $dataIn['data']['h'];
                if (empty($attackerArmyId) || (empty($h) && empty($s))) {
                    $this->sendError($user, 'Brak "armyId", "s" lub "h"!');
                    return;
                }

                $childArmyId = Cli_Database::splitArmy($user->parameters['gameId'], $h, $s, $attackerArmyId, $user->parameters['playerId'], $db);
                if (empty($childArmyId)) {
                    $this->sendError($user, 'Brak "childArmyId"');
                    return;
                }
                $token = array(
                    'type' => $dataIn['type'],
                    'data' => array(
                        'parentArmy' => Cli_Database::getArmyByArmyId($user->parameters['gameId'], $attackerArmyId, $db),
                        'childArmy' => Cli_Database::getArmyByArmyId($user->parameters['gameId'], $childArmyId, $db),
                    ),
                    'color' => Cli_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db)
                );

                $users = Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db);

                $this->sendToChannel($token, $users);

                break;

            case 'joinArmy':
                $armyId = $dataIn['data']['armyId'];
                if (empty($armyId)) {
                    $this->sendError($user, 'Brak "armyId"!');
                    return;
                }

                $position = Cli_Database::getArmyPositionByArmyId($user->parameters['gameId'], $armyId, $user->parameters['playerId'], $db);
                $armiesIds = Cli_Database::joinArmiesAtPosition($user->parameters['gameId'], $position, $user->parameters['playerId'], $db);

                if (empty($armyId)) {
                    $this->sendError($user, 'Brak "armyId"!');
                    return;
                }
                $token = array(
                    'type' => $dataIn['type'],
                    'army' => Cli_Database::getArmyByArmyId($user->parameters['gameId'], $armiesIds['armyId'], $db),
                    'deletedIds' => $armiesIds['deletedIds'],
                    'color' => Cli_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db)
                );

                $users = Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'disbandArmy':
                $armyId = $dataIn['data']['armyId'];
                if (empty($armyId)) {
                    $this->sendError($user, 'Brak "armyId"!');
                    return;
                }

                $destroyArmyResponse = Cli_Database::destroyArmy($user->parameters['gameId'], $armyId, $user->parameters['playerId'], $db);
                if (!$destroyArmyResponse) {
                    $this->sendError($user, 'Nie mogę usunąć armii!');
                    return;
                }

                $token = array(
                    'type' => $dataIn['type'],
                    'data' => array(
                        'armyId' => $armyId,
                        'x' => $dataIn['data']['x'],
                        'y' => $dataIn['data']['y']
                    ),
                    'color' => Cli_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db)
                );

                $users = Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'heroResurrection':
                $cId = $dataIn['data']['castleId'];
                if ($cId == null) {
                    $this->sendError($user, 'Brak "castleId"!');
                    return;
                }

                if (!Cli_Database::isPlayerCastle($user->parameters['gameId'], $cId, $user->parameters['playerId'], $db)) {
                    $this->sendError($user, 'To nie jest Twój zamek! ' . $cId);
                    return;
                }
                if (!Cli_Database::isHeroInGame($user->parameters['gameId'], $user->parameters['playerId'], $db)) {
                    Cli_Database::connectHero($user->parameters['gameId'], $user->parameters['playerId'], $db);
                }
                $heroId = Cli_Database::getDeadHeroId($user->parameters['gameId'], $user->parameters['playerId'], $db);
                if (!$heroId) {
                    $this->sendError($user, 'Twój heros żyje! ' . $heroId);
                    return;
                }
                $gold = Cli_Database::getPlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $db);
                if ($gold < 100) {
                    $this->sendError($user, 'Za mało złota!');
                    return;
                }
                $position = Application_Model_Board::getCastlePosition($cId);
                $armyId = Cli_Database::heroResurection($user->parameters['gameId'], $heroId, $position, $user->parameters['playerId'], $db);
                $gold -= 100;
                Cli_Database::updatePlayerInGameGold($user->parameters['gameId'], $user->parameters['playerId'], $gold, $db);

                $token = array(
                    'type' => $dataIn['type'],
                    'data' => array(
                        'army' => Cli_Database::getArmyByArmyId($user->parameters['gameId'], $armyId, $db),
                        'gold' => $gold
                    ),
                    'color' => Cli_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db)
                );

                $users = Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'ruin':
                $attackerArmyId = $dataIn['data']['armyId'];
                if (!Zend_Validate::is($attackerArmyId, 'Digits')) {
                    $this->sendError($user, 'Brak "armyId"!');
                    return;
                }

                $heroId = Cli_Database::getHeroIdByArmyIdPlayerId($user->parameters['gameId'], $attackerArmyId, $user->parameters['playerId'], $db);
                if (empty($heroId)) {
                    $this->sendError($user, 'Tylko Hero może przeszukiwać ruiny!');
                    return;
                }
                $position = Cli_Database::getArmyPositionByArmyId($user->parameters['gameId'], $attackerArmyId, $user->parameters['playerId'], $db);
                $ruinId = Application_Model_Board::confirmRuinPosition($position);
                if (!Zend_Validate::is($ruinId, 'Digits')) {
                    $this->sendError($user, 'Brak ruinId na pozycji');
                    return;
                }
                if (Cli_Database::ruinExists($user->parameters['gameId'], $ruinId, $db)) {
                    $this->sendError($user, 'Ruiny są już przeszukane.');
                    return;
                }

                $find = Cli_Database::searchRuin($user->parameters['gameId'], $ruinId, $heroId, $attackerArmyId, $user->parameters['playerId'], $db);

                if (Cli_Database::ruinExists($user->parameters['gameId'], $ruinId, $db)) {
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
                    'type' => $dataIn['type'],
                    'data' => array(
                        'army' => Cli_Database::getArmyByArmyId($user->parameters['gameId'], $attackerArmyId, $db),
                        'ruin' => $ruin,
                        'find' => $find
                    ),
                    'color' => Cli_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db)
                );

                $users = Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db);

                $this->sendToChannel($token, $users);

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

                $users = Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db);

                $this->sendToChannel($token, $users);
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

                $users = Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db);

                $this->sendToChannel($token, $users);
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

                $users = Cli_Database::getInGameWSSUIds($user->parameters['gameId'], $db);

                $this->sendToChannel($token, $users);
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
