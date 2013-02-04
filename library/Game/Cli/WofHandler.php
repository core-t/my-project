<?php

/**
 * This resource handler will respond to all messages sent to /wof/ on the socketserver below
 *
 * All this handler does is receiving data from browsers and sending the responds back
 * @author Bartosz Krzeszewski
 *
 */
class Game_Cli_WofHandler extends WebSocket_UriHandler {

    public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg) {

        $dataIn = Zend_Json::decode($msg->getData());
        print_r('ZAPYTANIE ');
        print_r($dataIn);

        $db = Game_Cli_Database::getDb();

        if ($dataIn['type'] == 'open') {
            $token = array(
                'type' => 'open',
                'wssuid' => $user->getId()
            );

            $this->send($user, Zend_Json::encode($token));
            return;
        }

        if (!isset($dataIn['gameId']) || !isset($dataIn['playerId'])) {
            $this->sendError($user, 'Brak "gameId" lub "playerId"');
            return;
        }

        if (!Game_Cli_Database::checkAccessKey($dataIn['gameId'], $dataIn['playerId'], $dataIn['accessKey'], $db)) {
            $this->sendError($user, 'Brak uprawnień!');
            return;
        }

        if ($dataIn['type'] == 'chat') {
            $token = array(
                'type' => $dataIn['type'],
                'msg' => $dataIn['data'],
                'color' => Game_Cli_Database::getPlayerColor($dataIn['gameId'], $dataIn['playerId'], $db)
            );

            $users = Game_Cli_Database::getInGameWSSUIdsExceptMine($dataIn['gameId'], $dataIn['playerId']);

            $this->sendToChannel($token, $users);
            return;
        } elseif ($dataIn['type'] == 'computer') {
            if (!Game_Cli_Database::isGameMaster($dataIn['gameId'], $dataIn['playerId'], $db)) {
                $this->sendError($user, 'Nie Twoja gra!');
                return;
            }
            $playerId = Game_Cli_Database::getTurnPlayerId($dataIn['gameId'], $db);
            if (!Game_Cli_Database::isComputer($playerId)) {
                $this->sendError($user, 'To nie komputer!');
                return;
            }

            if (!Game_Cli_Database::playerTurnActive($dataIn['gameId'], $playerId, $db)) {
                $token = Game_Cli_ComputerMainBlocks::startTurn($dataIn['gameId'], $playerId, $db);
            } else {
                $army = Game_Cli_Database::getComputerArmyToMove($dataIn['gameId'], $playerId, $db);
                if (!empty($army['armyId'])) {
                    $token = Game_Cli_ComputerMainBlocks::moveArmy($dataIn['gameId'], $playerId, $army, $db);
                } else {
                    $token = Game_Cli_ComputerMainBlocks::endTurn($dataIn['gameId'], $playerId, $db);
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
                    $token['type'] = 'computerEnd';
                    break;
                case 'gameover':
                    $token['type'] = 'computerGameover';
                    break;
            }

            $users = Game_Cli_Database::getInGameWSSUIds($dataIn['gameId'], $db);

            $this->sendToChannel($token, $users);

            return;
        }

        if (!Game_Cli_Database::isPlayerTurn($dataIn['gameId'], $dataIn['playerId'], $db)) {
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

                $army = Game_Cli_Database::getArmyByArmyIdPlayerId($dataIn['gameId'], $attackerArmyId, $dataIn['playerId'], $db);

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

                $fields = Game_Cli_Database::getEnemyArmiesFieldsPositions($dataIn['gameId'], $dataIn['playerId'], $db);
                $castlesSchema = Application_Model_Board::getCastlesSchema();
                $allCastles = Game_Cli_Database::getAllCastles($dataIn['gameId'], $db);

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
                    $enemy = Game_Cli_Database::getAllEnemyUnitsFromPosition($dataIn['gameId'], array('x' => $x, 'y' => $y), $dataIn['playerId'], $db);
                    if ($enemy['ids']) { // enemy army
                        $fields = Application_Model_Board::changeArmyField($fields, $x, $y, 'c');
                    } else { // idziemy nie walczymy
                        if (Game_Cli_Database::areMySwimmingUnitsAtPosition($dataIn['gameId'], array('x' => $x, 'y' => $y), $dataIn['playerId'], $db)) {
                            $fields = Application_Model_Board::changeArmyField($fields, $x, $y, 'b');
                        }
                    }
                }

                /*
                 * A* START
                 */

                $A_Star = new Game_Cli_Astar($x, $y);

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
                $movesLeft = $army['movesLeft'] - $move['currentPosition']['movesSpend'];

                if ($move['currentPosition']['x'] == $x && $move['currentPosition']['y'] == $y) {
                    if (Zend_Validate::is($castleId, 'Digits')) { // castle
                        if ($movesLeft >= 2) {
                            $fight = true;
                            if ($defenderColor == 'neutral') {
                                $enemy = Game_Cli_Battle::getNeutralCastleGarrizon($dataIn['gameId'], $db);
                            } else { // kolor wrogiego zamku sprawdzam dopiero wtedy gdy wiem, że armia ma na niego zasięg
                                $defenderColor = Game_Cli_Database::getColorByCastleId($dataIn['gameId'], $castleId, $db);
                                $enemy = Game_Cli_Database::getAllUnitsFromCastlePosition($dataIn['gameId'], Application_Model_Board::getCastlePosition($castleId), $db);
                            }
                        } else {
                            $rollbackPath = true;
                        }
                    } elseif ($enemy['ids']) { // enemy army
                        if ($movesLeft >= 2) {
                            $fight = true;
                            $defenderColor = Game_Cli_Database::getColorByArmyId($dataIn['gameId'], $enemy['ids'][0], $db);
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
                    $battle = new Game_Cli_Battle($army, $enemy);

                    if (Zend_Validate::is($castleId, 'Digits')) {
                        if ($defenderColor == 'neutral') {
                            $battle->fight();
                            $battle->updateArmies($dataIn['gameId'], $db);
                            $defender = $battle->getDefender();
                        } else {
                            $battle->addCastleDefenseModifier($dataIn['gameId'], $castleId, $db);
                            $battle->fight();
                            $battle->updateArmies($dataIn['gameId'], $db);
                            $castle = Application_Model_Board::getCastle($castleId);
                            $defender = Game_Cli_Database::updateAllArmiesFromCastlePosition($dataIn['gameId'], $castle['position'], $db);
                        }
                    } else {
                        $battle->addTowerDefenseModifier($x, $y);
                        $battle->fight();
                        $battle->updateArmies($dataIn['gameId'], $db);
                        $defender = Game_Cli_Database::updateAllArmiesFromPosition($dataIn['gameId'], array('x' => $x, 'y' => $y), $db);
                    }

                    if (empty($defender)) {
                        if (Zend_Validate::is($castleId, 'Digits')) {
                            if ($defenderColor == 'neutral') {
                                Game_Cli_Database::addCastle($dataIn['gameId'], $castleId, $dataIn['playerId'], $db);
                            } else {
                                Game_Cli_Database::changeOwner($dataIn['gameId'], $castleId, $dataIn['playerId'], $db);
                            }
                        }
                        $move['currentPosition']['movesSpend'] += 2;
                        Game_Cli_Database::updateArmyPosition($dataIn['gameId'], $attackerArmyId, $dataIn['playerId'], $move['currentPosition'], $db);
                        $attacker = Game_Cli_Database::getArmyByArmyIdPlayerId($dataIn['gameId'], $attackerArmyId, $dataIn['playerId'], $db);
                        $victory = true;
                        foreach ($enemy['ids'] as $id)
                        {
                            $defender[]['armyId'] = $id;
                        }
                    } else {
                        Game_Cli_Database::destroyArmy($dataIn['gameId'], $army['armyId'], $dataIn['playerId'], $db);
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
                    Game_Cli_Database::updateArmyPosition($dataIn['gameId'], $attackerArmyId, $dataIn['playerId'], $move['currentPosition']);
                    $armiesIds = Game_Cli_Database::joinArmiesAtPosition($dataIn['gameId'], $move['currentPosition'], $dataIn['playerId']);
                    $newArmyId = $armiesIds['armyId'];
                    $attacker = Game_Cli_Database::getArmyByArmyIdPlayerId($dataIn['gameId'], $newArmyId, $dataIn['playerId'], $db);
                    $deletedIds = $armiesIds['deletedIds'];
                }

                $token = array(
                    'type' => 'move',
                    'attackerColor' => Game_Cli_Database::getPlayerColor($dataIn['gameId'], $dataIn['playerId'], $db),
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

                $users = Game_Cli_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users, 1);
                break;

//            case 'armies':
//                $color = $dataIn['data']['color'];
//                if (empty($color)) {
//                    $this->sendError($user, 'Brak "color"!');
//                    return;
//                }
//
//                $playerId = Game_Cli_Database::getPlayerIdByColor($dataIn['gameId'], $color, $db);
//                if (empty($playerId)) {
//                    $this->sendError($user, 'Brak $playerId!');
//                    return;
//                }
//                $token = array(
//                    'type' => $dataIn['type'],
//                    'data' => Game_Cli_Database::getPlayerArmies($dataIn['gameId'], $playerId),
//                    'color' => Game_Cli_Database::getPlayerColor($dataIn['gameId'], $playerId, $db)
//                );
//
//                $users = Game_Cli_Database::getInGameWSSUIdsExceptMine($dataIn['gameId'], $dataIn['playerId'], $db);
//
//                $this->sendToChannel($token, $users);
//                break;

            case 'splitArmy':
                $attackerArmyId = $dataIn['data']['armyId'];
                $s = $dataIn['data']['s'];
                $h = $dataIn['data']['h'];
                if (empty($attackerArmyId) || (empty($h) && empty($s))) {
                    $this->sendError($user, 'Brak "armyId", "s" lub "h"!');
                    return;
                }

                $childArmyId = Game_Cli_Database::splitArmy($dataIn['gameId'], $h, $s, $attackerArmyId, $dataIn['playerId'], $db);
                if (empty($childArmyId)) {
                    $this->sendError($user, 'Brak "childArmyId"');
                    return;
                }
                $token = array(
                    'type' => $dataIn['type'],
                    'data' => array(
                        'parentArmy' => Game_Cli_Database::getArmyByArmyId($dataIn['gameId'], $attackerArmyId, $db),
                        'childArmy' => Game_Cli_Database::getArmyByArmyId($dataIn['gameId'], $childArmyId, $db),
                    ),
                    'color' => Game_Cli_Database::getPlayerColor($dataIn['gameId'], $dataIn['playerId'], $db)
                );

                $users = Game_Cli_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);

                break;

            case 'joinArmy':
                $armyId = $dataIn['data']['armyId'];
                if (empty($armyId)) {
                    $this->sendError($user, 'Brak "armyId"!');
                    return;
                }

                $position = Game_Cli_Database::getArmyPositionByArmyId($dataIn['gameId'], $armyId, $dataIn['playerId'], $db);
                $armiesIds = Game_Cli_Database::joinArmiesAtPosition($dataIn['gameId'], $position, $dataIn['playerId'], $db);

                if (empty($armyId)) {
                    $this->sendError($user, 'Brak "armyId"!');
                    return;
                }
                $token = array(
                    'type' => $dataIn['type'],
                    'army' => Game_Cli_Database::getArmyByArmyId($dataIn['gameId'], $armiesIds['armyId'], $db),
                    'deletedIds' => $armiesIds['deletedIds'],
                    'color' => Game_Cli_Database::getPlayerColor($dataIn['gameId'], $dataIn['playerId'], $db)
                );

                $users = Game_Cli_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'disbandArmy':
                $armyId = $dataIn['data']['armyId'];
                if (empty($armyId)) {
                    $this->sendError($user, 'Brak "armyId"!');
                    return;
                }

                $destroyArmyResponse = Game_Cli_Database::destroyArmy($dataIn['gameId'], $armyId, $dataIn['playerId'], $db);
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
                    'color' => Game_Cli_Database::getPlayerColor($dataIn['gameId'], $dataIn['playerId'], $db)
                );

                $users = Game_Cli_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'heroResurrection':
                $cId = $dataIn['data']['castleId'];
                if ($cId == null) {
                    $this->sendError($user, 'Brak "castleId"!');
                    return;
                }

                if (!Game_Cli_Database::isPlayerCastle($dataIn['gameId'], $cId, $dataIn['playerId'], $db)) {
                    $this->sendError($user, 'To nie jest Twój zamek! ' . $cId);
                    return;
                }
                if (!Game_Cli_Database::isHeroInGame($dataIn['gameId'], $dataIn['playerId'], $db)) {
                    Game_Cli_Database::connectHero($dataIn['gameId'], $dataIn['playerId'], $db);
                }
                $heroId = Game_Cli_Database::getDeadHeroId($dataIn['gameId'], $dataIn['playerId'], $db);
                if (!$heroId) {
                    $this->sendError($user, 'Twój heros żyje! ' . $heroId);
                    return;
                }
                $gold = Game_Cli_Database::getPlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $db);
                if ($gold < 100) {
                    $this->sendError($user, 'Za mało złota!');
                    return;
                }
                $position = Application_Model_Board::getCastlePosition($cId);
                $armyId = Game_Cli_Database::heroResurection($dataIn['gameId'], $heroId, $position, $dataIn['playerId'], $db);
                $gold -= 100;
                Game_Cli_Database::updatePlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $gold, $db);

                $token = array(
                    'type' => $dataIn['type'],
                    'data' => array(
                        'army' => Game_Cli_Database::getArmyByArmyId($dataIn['gameId'], $armyId, $db),
                        'gold' => $gold
                    ),
                    'color' => Game_Cli_Database::getPlayerColor($dataIn['gameId'], $dataIn['playerId'], $db)
                );

                $users = Game_Cli_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'ruin':
                $attackerArmyId = $dataIn['data']['armyId'];
                if (!Zend_Validate::is($attackerArmyId, 'Digits')) {
                    $this->sendError($user, 'Brak "armyId"!');
                    return;
                }

                $heroId = Game_Cli_Database::getHeroIdByArmyIdPlayerId($dataIn['gameId'], $attackerArmyId, $dataIn['playerId'], $db);
                if (empty($heroId)) {
                    $this->sendError($user, 'Tylko Hero może przeszukiwać ruiny!');
                    return;
                }
                $position = Game_Cli_Database::getArmyPositionByArmyId($dataIn['gameId'], $attackerArmyId, $dataIn['playerId'], $db);
                $ruinId = Application_Model_Board::confirmRuinPosition($position);
                if (!Zend_Validate::is($ruinId, 'Digits')) {
                    $this->sendError($user, 'Brak ruinId na pozycji');
                    return;
                }
                if (Game_Cli_Database::ruinExists($dataIn['gameId'], $ruinId, $db)) {
                    $this->sendError($user, 'Ruiny są już przeszukane.');
                    return;
                }

                $find = Game_Cli_Database::searchRuin($dataIn['gameId'], $ruinId, $heroId, $attackerArmyId, $dataIn['playerId'], $db);

                if (Game_Cli_Database::ruinExists($dataIn['gameId'], $ruinId, $db)) {
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
                        'army' => Game_Cli_Database::getArmyByArmyId($dataIn['gameId'], $attackerArmyId, $db),
                        'ruin' => $ruin,
                        'find' => $find
                    ),
                    'color' => Game_Cli_Database::getPlayerColor($dataIn['gameId'], $dataIn['playerId'], $db)
                );

                $users = Game_Cli_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users, 1);

                break;

            case 'nextTurn':
                $token = Game_Cli_Turn::next($dataIn['gameId'], $dataIn['playerId'], $db);
                $token['type'] = $dataIn['type'];

                $users = Game_Cli_Database::getInGameWSSUIds($dataIn['gameId']);

                $this->sendToChannel($token, $users);
                break;

            case 'startTurn':
                $token = Game_Cli_Turn::start($dataIn['gameId'], $dataIn['playerId'], $db);
                $token['type'] = $dataIn['type'];

                $users = Game_Cli_Database::getInGameWSSUIds($dataIn['gameId']);

                $this->sendToChannel($token, $users);
                break;

            case 'razeCastle':
                $cId = $dataIn['data']['castleId'];
                if ($cId == null) {
                    $this->sendError($user, 'Brak "castleId"!');
                    return;
                }

                Game_Cli_Database::razeCastle($dataIn['gameId'], $cId, $dataIn['playerId'], $db);
                $gold = Game_Cli_Database::getPlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $db) + 1000;
                Game_Cli_Database::updatePlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $gold, $db);
                $token = Game_Cli_Database::getCastle($dataIn['gameId'], $cId, $db);
                $token['color'] = Game_Cli_Database::getPlayerColor($dataIn['gameId'], $dataIn['playerId'], $db);
                $token['gold'] = $gold;
                $token['type'] = 'castle';

                $users = Game_Cli_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);
                break;

            case 'castleBuildDefense':
                $cId = $dataIn['data']['castleId'];
                if ($cId == null) {
                    $this->sendError($user, 'Brak "castleId"!');
                    return;
                }

                if (!Game_Cli_Database::isPlayerCastle($dataIn['gameId'], $cId, $dataIn['playerId'], $db)) {
                    $this->sendError($user, 'To nie jest Twój zamek.');
                    break;
                }
                $gold = Game_Cli_Database::getPlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $db);
                $defenseModifier = Game_Cli_Database::getCastleDefenseModifier($dataIn['gameId'], $cId, $db);
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
                Game_Cli_Database::buildDefense($dataIn['gameId'], $cId, $dataIn['playerId'], $db);
                $token = Game_Cli_Database::getCastle($dataIn['gameId'], $cId, $db);
                $token['defensePoints'] = $defensePoints;
                $token['color'] = Game_Cli_Database::getPlayerColor($dataIn['gameId'], $dataIn['playerId'], $db);
                $token['gold'] = $gold - $costs;
                $token['type'] = 'castle';
                Game_Cli_Database::updatePlayerInGameGold($dataIn['gameId'], $dataIn['playerId'], $token['gold'], $db);

                $users = Game_Cli_Database::getInGameWSSUIds($dataIn['gameId'], $db);

                $this->sendToChannel($token, $users);
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

}
