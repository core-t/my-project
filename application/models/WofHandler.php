<?php

/**
 * This resource handler will respond to all messages sent to /wof/ on the socketserver below
 *
 * All this handler does is receiving data from browsers and sending the responds back
 * @author Bartosz Krzeszewski
 *
 */
class Application_Model_WofHandler extends WebSocket_UriHandler {

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {

        $dataIn = Zend_Json::decode($msg->getData());
        print_r('ZAPYTANIE ');
        print_r($dataIn);

        $db = Application_Model_Database::getDb();

        switch ($dataIn['type'])
        {
            case 'move':
                if (!isset($dataIn['gameId']) || !isset($dataIn['playerId'])) {
                    $this->sendError($user, 'Brak "gameId" lub "playerId"');
                    return;
                }

                if (!Application_Model_Database::isPlayerTurn($dataIn['gameId'], $dataIn['playerId'], $db)) {
                    $this->sendError($user, 'Nie Twoja tura.');
                    return;
                }

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

                $army = Application_Model_Database::getArmyByArmyIdPlayerId($dataIn['gameId'], $attackerArmyId, $dataIn['playerId'], $db);

                if (empty($army)) {
                    $this->sendError($user, 'Brak armii o podanym ID!');
                    return;
                }

                $canFly = -count($army['heroes']);
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

                $fields = Application_Model_Database::getEnemyArmiesFieldsPositions($dataIn['gameId'], $dataIn['playerId'], $db);
                $castlesSchema = Application_Model_Board::getCastlesSchema();
                $allCastles = Application_Model_Database::getAllCastles($dataIn['gameId'], $db);

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

                    if ($dataIn['playerId'] == $allCastles[$cId]['playerId']) { // my castle
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
                    $enemy = Application_Model_Database::getAllEnemyUnitsFromPosition($dataIn['gameId'], array('x' => $x, 'y' => $y), $dataIn['playerId'], $db);
                    if ($enemy['ids']) { // enemy army
                        $fields = Application_Model_Board::changeArmyField($fields, $x, $y, 'c');
                    } else { // idziemy nie walczymy
                        if (Application_Model_Database::areMySwimmingUnitsAtPosition($dataIn['gameId'], array('x' => $x, 'y' => $y), $dataIn['playerId'], $db)) {
                            echo 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb';
                            $fields = Application_Model_Board::changeArmyField($fields, $x, $y, 'b');
                        }
                    }
                }

                /*
                 * A* START
                 */

                $A_Star = new Game_Astar($x, $y);

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
                    $this->sendError($user, 'Nie wykonano ruchu');
                    return;
                }

                if ($move['currentPosition']['movesSpend'] > $army['movesLeft']) {
                    $this->sendError($user, 'Próba wykonania większej ilości ruchów niż jednostka posiada');
                    return;
                }

                $fight = false;

                print_r($move);
                var_dump($x);
                var_dump($y);
                var_dump($army['movesLeft']);
                $movesLeft = $army['movesLeft'] - $move['currentPosition']['movesSpend'];
                var_dump($movesLeft);

                if ($move['currentPosition']['x'] == $x && $move['currentPosition']['y'] == $y) {
                    if (Zend_Validate::is($castleId, 'Digits')) { // castle
                        if ($movesLeft >= 2) {
                            $fight = true;
                            if ($defenderColor == 'neutral') {
                                $enemy = Game_Battle::getNeutralCastleGarrizon($dataIn['gameId'], $db);
                            } else { // kolor wrogiego zamku sprawdzam dopiero wtedy gdy wiem, że armia ma na niego zasięg
                                $defenderColor = Application_Model_Database::getColorByCastleId($dataIn['gameId'], $castleId, $db);
                                $enemy = Application_Model_Database::getAllUnitsFromCastlePosition($dataIn['gameId'], Application_Model_Board::getCastlePosition($castleId), $db);
                            }
                        } else {
                            $rollbackPath = true;
                        }
                    } elseif ($enemy['ids']) { // enemy army
                        if ($movesLeft >= 2) {
                            $fight = true;
                            $defenderColor = Application_Model_Database::getColorByArmyId($dataIn['gameId'], $enemy['ids'][0], $db);
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
                    $battle = new Game_Battle($army, $enemy);
                    $battle->fight();
                    $battle->updateArmies($dataIn['gameId'], $db);

                    if (Zend_Validate::is($castleId, 'Digits')) {
                        if ($defenderColor == 'neutral') {
                            $defender = $battle->getDefender();
                        } else {
                            $castle = Application_Model_Board::getCastle($castleId);
                            $defender = Application_Model_Database::updateAllArmiesFromCastlePosition($dataIn['gameId'], $castle['position'], $db);
                        }
                    } else {
                        $defender = Application_Model_Database::updateAllArmiesFromPosition($dataIn['gameId'], array('x' => $x, 'y' => $y), $db);
                    }

                    if (empty($defender)) {
                        if (Zend_Validate::is($castleId, 'Digits')) {
                            if ($defenderColor == 'neutral') {
                                Application_Model_Database::addCastle($dataIn['gameId'], $castleId, $dataIn['playerId'], $db);
                            } else {
                                Application_Model_Database::changeOwner($dataIn['gameId'], $castleId, $dataIn['playerId'], $db);
                            }
                        }
                        $move['currentPosition']['movesSpend'] += 2;
                        Application_Model_Database::updateArmyPosition($dataIn['gameId'], $attackerArmyId, $dataIn['playerId'], $move['currentPosition'], $db);
                        $attacker = Application_Model_Database::getArmyByArmyIdPlayerId($dataIn['gameId'], $attackerArmyId, $dataIn['playerId'], $db);
                        $victory = true;
                        foreach ($enemy['ids'] as $id)
                        {
                            $defender[]['armyId'] = $id;
                        }
                    } else {
                        Application_Model_Database::destroyArmy($dataIn['gameId'], $army['armyId'], $dataIn['playerId'], $db);
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
                    Application_Model_Database::updateArmyPosition($dataIn['gameId'], $attackerArmyId, $dataIn['playerId'], $move['currentPosition']);
                    $armiesIds = Application_Model_Database::joinArmiesAtPosition($dataIn['gameId'], $move['currentPosition'], $dataIn['playerId']);
                    $newArmyId = $armiesIds['armyId'];
                    $attacker = Application_Model_Database::getArmyByArmyIdPlayerId($dataIn['gameId'], $newArmyId, $dataIn['playerId'], $db);
                    $deletedIds = $armiesIds['deletedIds'];
                }

                $token = array(
                    'type' => 'move',
                    'playerId' => $dataIn['playerId'],
                    'attackerColor' => $dataIn['color'],
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

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users, 1);
                break;

            case 'chat':
                $this->chat($dataIn);
                break;

            case 'armies':
                $color = $dataIn['data']['color'];
                if (empty($color)) {
                    $this->sendError($user, 'Brak "color"!');
                    return;
                }

                $playerId = Application_Model_Database::getPlayerIdByColor($dataIn['gameId'], $color, $db);
                if (empty($playerId)) {
                    $this->sendError($user, 'Brak $playerId!');
                    return;
                }
                $token = array(
                    'type' => $dataIn['type'],
                    'data' => Application_Model_Database::getPlayerArmies($dataIn['gameId'], $playerId),
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color']
                );

                $users = Application_Model_Database::getInGameWSSUIdsExceptMine($dataIn['gameId'], $dataIn['playerId'], $db);

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

                $childArmyId = Application_Model_Database::splitArmy($dataIn['gameId'], $h, $s, $attackerArmyId, $dataIn['playerId'], $db);
                if (empty($childArmyId)) {
                    $this->sendError($user, 'Brak "childArmyId"');
                    return;
                }
                $token = array(
                    'type' => $dataIn['type'],
                    'data' => array(
                        'parentArmy' => Application_Model_Database::getArmyByArmyId($dataIn['gameId'], $attackerArmyId, $db),
                        'childArmy' => Application_Model_Database::getArmyByArmyId($dataIn['gameId'], $childArmyId, $db),
                    ),
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color']
                );

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);

                break;

            case 'joinArmy':
                $armyId1 = $dataIn['data']['armyId1'];
                $armyId2 = $dataIn['data']['armyId2'];
                if (empty($armyId1) || empty($armyId2)) {
                    $this->sendError($user, 'Brak "armyId1" i "armyId2"!');
                    return;
                }

                $position1 = Application_Model_Database::getArmyPositionByArmyId($dataIn['gameId'], $armyId1, $dataIn['playerId'], $db);
                $position2 = Application_Model_Database::getArmyPositionByArmyId($dataIn['gameId'], $armyId2, $dataIn['playerId'], $db);
                if (empty($position1['x']) || empty($position1['y']) || ($position1['x'] != $position2['x']) || ($position1['y'] != $position2['y'])) {
                    $this->sendError($user, 'Armie nie są na tej samej pozycji!');
                    return;
                }
                $armiesIds = Application_Model_Database::joinArmiesAtPosition($dataIn['gameId'], $position1, $dataIn['playerId'], $db);
                $armyId = $armiesIds['armyId'];

                if (empty($armyId)) {
                    $this->sendError($user, 'Brak "armyId"!');
                    return;
                }
                $token = array(
                    'type' => $dataIn['type'],
                    'data' => array(
                        'army' => Application_Model_Database::getArmyByArmyId($dataIn['gameId'], $armyId, $db),
                        'deletedIds' => $armiesIds['deletedIds']
                    ),
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color']
                );

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'disbandArmy':
                $armyId = $dataIn['data']['armyId'];
                if (empty($armyId)) {
                    $this->sendError($user, 'Brak "armyId"!');
                    return;
                }

                $destroyArmyResponse = Application_Model_Database::destroyArmy($dataIn['gameId'], $armyId, $dataIn['playerId'], $db);
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
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color']
                );

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'heroResurrection':
                $cId = $dataIn['data']['castleId'];
                if ($cId == null) {
                    $this->sendError($user, 'Brak "castleId"!');
                    return;
                }

                if (!Application_Model_Database::isPlayerCastle($dataIn['gameId'], $cId, $dataIn['playerId'], $db)) {
                    $this->sendError($user, 'To nie jest Twój zamek! ' . $cId);
                    return;
                }
                if (!Application_Model_Database::isHeroInGame($dataIn['gameId'], $dataIn['playerId'], $db)) {
                    Application_Model_Database::connectHero($dataIn['gameId'], $dataIn['playerId'], $db);
                }
                $heroId = Application_Model_Database::getDeadHeroId($dataIn['gameId'], $dataIn['playerId'], $db);
                if (!$heroId) {
                    $this->sendError($user, 'Twój heros żyje! ' . $heroId);
                    return;
                }
                $gold = Application_Model_Database::getPlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $db);
                if ($gold < 100) {
                    $this->sendError($user, 'Za mało złota!');
                    return;
                }
                $position = Application_Model_Board::getCastlePosition($cId);
                $armyId = Application_Model_Database::heroResurection($dataIn['gameId'], $heroId, $position, $dataIn['playerId'], $db);
                $gold -= 100;
                Application_Model_Database::updatePlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $gold, $db);

                $token = array(
                    'type' => $dataIn['type'],
                    'data' => array(
                        'army' => Application_Model_Database::getArmyByArmyId($dataIn['gameId'], $armyId, $db),
                        'gold' => $gold
                    ),
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color']
                );

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'ruin':
                $attackerArmyId = $dataIn['data']['armyId'];
                if (!Zend_Validate::is($attackerArmyId, 'Digits')) {
                    $this->sendError($user, 'Brak "armyId"!');
                    return;
                }

                $heroId = Application_Model_Database::getHeroIdByArmyIdPlayerId($dataIn['gameId'], $attackerArmyId, $dataIn['playerId'], $db);
                if (empty($heroId)) {
                    echo('Brak heroId. Tylko Hero może przeszukiwać ruiny!');
                    return;
                }
                $position = Application_Model_Database::getArmyPositionByArmyId($dataIn['gameId'], $attackerArmyId, $dataIn['playerId'], $db);
                $ruinId = Application_Model_Board::confirmRuinPosition($position);
                if (!Zend_Validate::is($ruinId, 'Digits')) {
                    echo('Brak ruinId na pozycji');
                    return;
                }
                if (Application_Model_Database::ruinExists($dataIn['gameId'], $ruinId, $db)) {
                    echo('Ruiny są już przeszukane. ' . $ruinId . ' ' . $attackerArmyId);
                    return;
                }

                $find = Application_Model_Database::searchRuin($dataIn['gameId'], $ruinId, $heroId, $attackerArmyId, $dataIn['playerId'], $db);

                if (Application_Model_Database::ruinExists($dataIn['gameId'], $ruinId, $db)) {
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
                        'army' => Application_Model_Database::getArmyByArmyId($dataIn['gameId'], $attackerArmyId, $db),
                        'ruin' => $ruin,
                        'find' => $find
                    ),
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color']
                );

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users, 1);

                break;

            case 'turn':
                $token = array(
                    'type' => $dataIn['type'],
                    'data' => Application_Model_Turn::next($dataIn['gameId'], $dataIn['playerId'], $dataIn['color']),
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color']
                );

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId']);

                $this->sendToChannel($token, $users);
                break;

            case 'razeCastle':
                $cId = $dataIn['data']['castleId'];
                if ($cId == null) {
                    echo('Brak "castleId"!');
                    return;
                }

                $razeCastleResult = Application_Model_Database::razeCastle($dataIn['gameId'], $cId, $dataIn['playerId'], $db);
                switch ($razeCastleResult)
                {
                    case 1:
                        $gold = Application_Model_Database::getPlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $db) + 1000;
                        Application_Model_Database::updatePlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $gold, $db);
                        $response = Application_Model_Database::getCastle($dataIn['gameId'], $cId, $db);
                        $response['color'] = $dataIn['color'];
                        $response['gold'] = $gold;
                        $token = array(
                            'type' => 'castle',
                            'playerId' => $dataIn['playerId'],
                            'color' => $dataIn['color'],
                            'data' => $response
                        );

                        $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

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
                $cId = $dataIn['data']['castleId'];
                if ($cId == null) {
                    echo('Brak "castleId"!');
                    return;
                }

                if (!Application_Model_Database::isPlayerCastle($dataIn['gameId'], $cId, $dataIn['playerId'], $db)) {
                    echo('Nie jest Twój zamek.');
                    break;
                }
                $gold = Application_Model_Database::getPlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $db);
                $defenseModifier = Application_Model_Database::getCastleDefenseModifier($dataIn['gameId'], $cId, $db);
                $defensePoints = Application_Model_Board::getCastleDefense($cId);
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
                Application_Model_Database::buildDefense($dataIn['gameId'], $cId, $dataIn['playerId'], $db);
                $response = Application_Model_Database::getCastle($dataIn['gameId'], $cId, $db);
                $response['defensePoints'] = $defensePoints;
                $response['color'] = $dataIn['color'];
                $response['gold'] = $gold - $costs;
                Application_Model_Database::updatePlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $response['gold'], $db);

                $token = array(
                    'type' => 'castle',
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color'],
                    'data' => $response
                );

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'computer':

                if (!Application_Model_Database::isGameMaster($dataIn['gameId'], $dataIn['playerId'], $db)) {
                    echo('Nie Twoja gra!');
                    return;
                }
                $playerId = Application_Model_Database::getTurnPlayerId($dataIn['gameId'], $db);
                if (!Application_Model_Database::isComputer($playerId)) {
                    echo('To nie komputer!');
                    return;
                }

                if (!Application_Model_Database::playerTurnActive($dataIn['gameId'], $playerId, $db)) {
                    $response = Application_Model_Computer::startTurn($dataIn['gameId'], $playerId, $db);
                    var_dump($response['action'] . 'aaaa');
                } else {
                    $army = Application_Model_Database::getComputerArmyToMove($dataIn['gameId'], $playerId, $db);
                    if (!empty($army['armyId'])) {
                        $response = Application_Model_Computer::moveArmy($dataIn['gameId'], $playerId, $army, $db);
                        var_dump($response['action'] . 'cccc');
                    } else {
                        $response = Application_Model_Computer::endTurn($dataIn['gameId'], $playerId, $db);
                        var_dump($response['action'] . 'dddd');
                    }
                }

//                print_r($response);

                switch ($response['action'])
                {
                    case 'continue':
                        $type = 'computer';
                        break;
                    case 'start':
                        $type = 'computerStart';
                        break;
                    case 'end':
                        $type = 'computerEnd';
                        break;
                    case 'gameover':
                        $type = 'computerGameover';
                        break;
                }


                $token = array(
                    'type' => $type,
                    'playerId' => $dataIn['playerId'],
                    'color' => $dataIn['color'],
                    'data' => $response
                );

                $users = Application_Model_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users, 1);

                break;

            case 'open':
                $this->open($user);
                break;
        }
    }

    public function sendToChannel($token, $users, $debug = null) {
        if ($debug) {
            print_r('ODPOWIEDŹ ');
            print_r($token);
        }
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

    public function sendError($user, $msg, $debug = null) {
        $token = array(
            'type' => 'error',
            'msg' => $msg
        );
        if ($debug) {
            print_r('ODPOWIEDŹ ');
            print_r($token);
        }
        $this->send($user, Zend_Json::encode($token));
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

//    private function calculateArmiesDistance($dX, $dY, $aX, $aY, $castleId = null) {
//        echo '$dX,$dY=' . $dX . ',' . $dY . ' $aX,$aY=' . $aX . ',' . $aY;
//        $distance = sqrt(pow($aX - $dX, 2) + pow($aY - $dY, 2));
//        var_dump($distance);
//        if ($castleId !== null) {
//            $tmp = sqrt(pow($aX - $dX + 1, 2) + pow($aY - $dY, 2));
//            var_dump($tmp);
//            if ($tmp < $distance) {
//                $distance = $tmp;
//            }
//            $tmp = sqrt(pow($aX - $dX, 2) + pow($aY - $dY + 1, 2));
//            var_dump($tmp);
//            if ($tmp < $distance) {
//                $distance = $tmp;
//            }
//            $tmp = sqrt(pow($aX - $dX + 1, 2) + pow($aY - $dY + 1, 2));
//            var_dump($tmp);
//            if ($tmp < $distance) {
//                $distance = $tmp;
//            }
//            var_dump($distance);
//        }
//
//        return $distance;
//    }
//    private function movesSpend($x, $y, $army) {
//        $canFly = 1;
//        $canSwim = 0;
//        $movesRequiredToAttack = 1;
//        $canFly -= count($army['heroes']);
//        foreach ($army['soldiers'] as $soldier)
//        {
//            if ($soldier['canFly']) {
//                $canFly++;
//            } else {
//                $canFly -= 200;
//            }
//            if ($soldier['canSwim']) {
//                $canSwim++;
//            }
//        }
//        $fields = Application_Model_Board::getBoardFields();
//        $terrainType = $fields[$y][$x];
//        $terrain = Application_Model_Board::getTerrain($terrainType, $canFly, $canSwim);
//        return $terrain[1] + $movesRequiredToAttack;
//    }
}
