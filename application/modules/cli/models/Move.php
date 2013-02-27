<?php

class Cli_Model_Move
{

    public function __construct($attackerArmyId, $x, $y, $user, $db, $gameHandler)
    {
        if (!Zend_Validate::is($attackerArmyId, 'Digits') || !Zend_Validate::is($x, 'Digits') || !Zend_Validate::is($y, 'Digits')) {
            $gameHandler->sendError($user, 'Niepoprawny format danych ("armyId", "x", "y")!');
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

        $army = Cli_Model_Database::getArmy($user->parameters['gameId'], $attackerArmyId, $user->parameters['playerId'], $db);

        if (empty($army)) {
            $gameHandler->sendError($user, 'Brak armii o podanym ID! Odświerz przeglądarkę.');
            return;
        } else {
            $mArmy = new Cli_Model_Army($army);
            $army = $mArmy->getArmy();
        }

        $fields = Cli_Model_Database::getEnemyArmiesFieldsPositions($user->parameters['gameId'], $user->parameters['playerId'], $db);
        $castlesSchema = Application_Model_Board::getCastlesSchema();
        $allCastles = Cli_Model_Database::getAllCastles($user->parameters['gameId'], $db);

        $aP = array(
            'x' => $x,
            'y' => $y
        );

        foreach ($castlesSchema as $cId => $castle) {
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
            $enemy = Cli_Model_Database::getAllEnemyUnitsFromPosition($user->parameters['gameId'], array('x' => $x, 'y' => $y), $user->parameters['playerId'], $db);
            if ($enemy['ids']) { // enemy army
                $fields = Application_Model_Board::changeArmyField($fields, $x, $y, 'c');
            } else { // idziemy nie walczymy
                if (Cli_Model_Database::areMySwimmingUnitsAtPosition($user->parameters['gameId'], array('x' => $x, 'y' => $y), $user->parameters['playerId'], $db)) {
                    $fields = Application_Model_Board::changeArmyField($fields, $x, $y, 'b');
                }
            }
        }

        /*
         * A* START
         */


        try {
            $A_Star = new Cli_Model_Astar($army, $x, $y, $fields);
            $mArmy->calculateMovesSpend($A_Star->getPath($x . '_' . $y));

            print_r($move);
            exit;
        } catch (Exception $e) {
            echo($e);
            $gameHandler->sendError($user, 'Wystąpił błąd podczas obliczania ścieżki');
            return;
        }

        /*
         * A* END
         */

        if (!$move['currentPosition']) {
            $gameHandler->sendError($user, 'Za mało punktów ruchu aby wykonać akcję');
            return;
        }

        if ($move['currentPosition']['movesSpend'] > $army['movesLeft']) {
            $msg = 'Próba wykonania większej ilości ruchów niż jednostka posiada';
            echo($msg);
            $gameHandler->sendError($user, $msg);
            return;
        }

        $fight = false;
        $movesLeft = $army['movesLeft'] - $move['currentPosition']['movesSpend'];

        if ($move['currentPosition']['x'] == $x && $move['currentPosition']['y'] == $y) {
            if (Zend_Validate::is($castleId, 'Digits')) { // castle
                if ($movesLeft >= 2) {
                    $fight = true;
                    if ($defenderColor == 'neutral') {
                        $enemy = Cli_Model_Battle::getNeutralCastleGarrizon($user->parameters['gameId'], $db);
                    } else { // kolor wrogiego zamku sprawdzam dopiero wtedy gdy wiem, że armia ma na niego zasięg
                        $defenderColor = Cli_Model_Database::getColorByCastleId($user->parameters['gameId'], $castleId, $db);
                        $enemy = Cli_Model_Database::getAllEnemyUnitsFromCastlePosition($user->parameters['gameId'], Application_Model_Board::getCastlePosition($castleId), $db);
                    }
                } else {
                    $rollbackPath = true;
                }
            } elseif ($enemy['ids']) { // enemy army
                if ($movesLeft >= 2) {
                    $fight = true;
                    $defenderColor = Cli_Model_Database::getColorByArmyId($user->parameters['gameId'], $enemy['ids'][0], $db);
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
            $battle = new Cli_Model_Battle($army, $enemy);
//            $battle->setCombatAttackModifiers($army);
            $battle->setCombatDefenseModifiers($enemy);

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
                    $defender = Cli_Model_Database::getDefenderFromCastlePosition($user->parameters['gameId'], $castle['position'], $db);
                }
            } else {
                $battle->addTowerDefenseModifier($x, $y);
                $battle->fight();
                $battle->updateArmies($user->parameters['gameId'], $db);
                $defender = Cli_Model_Database::getDefenderFromPosition($user->parameters['gameId'], array('x' => $x, 'y' => $y), $db);
            }

            if (empty($defender)) {
                if (Zend_Validate::is($castleId, 'Digits')) {
                    if ($defenderColor == 'neutral') {
                        Cli_Model_Database::addCastle($user->parameters['gameId'], $castleId, $user->parameters['playerId'], $db);
                    } else {
                        Cli_Model_Database::changeOwner($user->parameters['gameId'], $castleId, $user->parameters['playerId'], $db);
                    }
                }
                Cli_Model_Database::updateArmyPosition($user->parameters['gameId'], $user->parameters['playerId'], $move['path'], $fields, $army, $db, true);
                $attacker = Cli_Model_Database::getArmyByArmyIdPlayerId($user->parameters['gameId'], $attackerArmyId, $user->parameters['playerId'], $db);
                $victory = true;
                foreach ($enemy['ids'] as $id) {
                    $defender[]['armyId'] = $id;
                }
            } else {
                Cli_Model_Database::destroyArmy($user->parameters['gameId'], $army['armyId'], $user->parameters['playerId'], $db);
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
                        $gameHandler->sendError($user, 'Nie wykonano ruchu');
                        return;
                    }
                    $newMove = Application_Model_Board::rewindPathOutOfCastle($move['path'], $castleId);
                    $newMove['currentPosition']['movesSpend'] = $move['currentPosition']['movesSpend'];
                    $move = $newMove;
                } else {
                    array_pop($move['path']);
                    if (!$move['currentPosition']['movesSpend']) {
                        $gameHandler->sendError($user, 'Nie wykonano ruchu');
                        return;
                    }
                    $count = count($move['path']);
                    $move['currentPosition']['x'] = $move['path'][$count]['x'];
                    $move['currentPosition']['y'] = $move['path'][$count]['y'];
                }
            }
            Cli_Model_Database::updateArmyPosition($user->parameters['gameId'], $user->parameters['playerId'], $move['path'], $fields, $army, $db);
            $armiesIds = Cli_Model_Database::joinArmiesAtPosition($user->parameters['gameId'], $move['currentPosition'], $user->parameters['playerId'], $db);
            $newArmyId = $armiesIds['armyId'];
            $attacker = Cli_Model_Database::getArmyByArmyIdPlayerId($user->parameters['gameId'], $newArmyId, $user->parameters['playerId'], $db);
            $deletedIds = $armiesIds['deletedIds'];
        }

        $token = array(
            'type' => 'move',
            'attackerColor' => Cli_Model_Database::getColorByPlayerId($user->parameters['gameId'], $user->parameters['playerId'], $db),
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

        $users = Cli_Model_Database::getInGameWSSUIds($user->parameters['gameId'], $db);

        $gameHandler->sendToChannel($token, $users);
    }

}