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

        if ($fields[$y][$x]['type'] == 'w') {
            if ($army['canSwim'] || $army['canFly']) {
                $otherArmyId = Cli_Model_Database::isOtherArmyAtPosition($user->parameters['gameId'], $attackerArmyId, $x, $y, $db);
                if ($otherArmyId) {
                    $otherArmy = Cli_Model_Database::getArmy($user->parameters['gameId'], $otherArmyId, $user->parameters['playerId'], $db);
                    $mOtherArmy = new Cli_Model_Army($otherArmy);
                    if (!$mOtherArmy->canSwim() && !$mOtherArmy->canFly()) {
                        $gameHandler->sendError($user, 'Nie możesz zostawić armii na wodzie.');
                        return;
                    }
                }
            }
        }

        $castlesSchema = Zend_Registry::get('castles');
        $allCastles = Cli_Model_Database::getAllCastles($user->parameters['gameId'], $db);

        $aP = array(
            'x' => $x,
            'y' => $y
        );

        foreach ($castlesSchema as $cId => $castle) {
            if (!isset($allCastles[$cId])) { // castle is neutral
                if (Application_Model_Board::isCastleField($aP, $castle['position'])) { // trakuję neutralny zamek jak własny ponieważ go atakuję i jeśli wygram to będę mógł po nim chodzić
                    $fields = Application_Model_Board::changeCasteFields($fields, $castle['position']['x'], $castle['position']['y'], 'E');
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
                if (Application_Model_Board::isCastleField($aP, $castle['position'])) { // trakuję zamek wroga jak własny ponieważ go atakuję i jeśli wygram to będę mógł po nim chodzić
                    $fields = Application_Model_Board::changeCasteFields($fields, $castle['position']['x'], $castle['position']['y'], 'E');
                    $castleId = $cId;
                } else {
                    $fields = Application_Model_Board::changeCasteFields($fields, $castle['position']['x'], $castle['position']['y'], 'e');
                }
            }
        }

        if ($castleId === null) {
            $enemy = Cli_Model_Database::getAllEnemyUnitsFromPosition($user->parameters['gameId'], array('x' => $x, 'y' => $y), $user->parameters['playerId'], $db);
            if ($enemy['ids']) { // enemy army
                $fields = Application_Model_Board::changeArmyField($fields, $x, $y, 'E');
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
            $move = $mArmy->calculateMovesSpend($A_Star->getPath($x . '_' . $y));
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

//        if ($move['movesSpend'] > $army['movesLeft']) {
//            $msg = 'Próba wykonania większej ilości ruchów niż jednostka posiada';
//            echo($msg);
//            $gameHandler->sendError($user, $msg);
//            return;
//        }

        $fight = false;

        if (Zend_Validate::is($castleId, 'Digits') && Application_Model_Board::isCastleField($move['currentPosition'], $castlesSchema[$castleId]['position'])) { // castle
            $fight = true;
            if ($defenderColor == 'neutral') {
                $enemy = Cli_Model_Battle::getNeutralCastleGarrison($user->parameters['gameId'], $db);
            } else { // kolor wrogiego zamku sprawdzam dopiero wtedy gdy wiem, że armia ma na niego zasięg
                $defenderColor = Cli_Model_Database::getColorByCastleId($user->parameters['gameId'], $castleId, $db);
                $enemy = Cli_Model_Database::getAllEnemyUnitsFromCastlePosition($user->parameters['gameId'], $castlesSchema[$castleId]['position'], $db);
                $enemy = Cli_Model_Army::addCastleDefenseModifier($enemy, $user->parameters['gameId'], $castleId, $db);
            }
        } elseif ($move['currentPosition']['x'] == $x && $move['currentPosition']['y'] == $y && $enemy['ids']) { // enemy army
            $fight = true;
            $defenderColor = Cli_Model_Database::getColorByArmyId($user->parameters['gameId'], $enemy['ids'][0], $db);
            $enemy['x'] = $x;
            $enemy['y'] = $y;
            $enemy = Cli_Model_Army::setCombatDefenseModifiers($enemy);
            $enemy = Cli_Model_Army::addTowerDefenseModifier($enemy);
        }

        /* ------------------------------------
         *
         * ZMIANY ZAPISUJĘ PONIZEJ TEJ LINII
         *
         * ------------------------------------ */

        if ($fight) {
            $battle = new Cli_Model_Battle($army, $enemy);
            $battle->fight();
            $battle->updateArmies($user->parameters['gameId'], $db);

            if (Zend_Validate::is($castleId, 'Digits')) {
                if ($defenderColor == 'neutral') {
                    $defender = $battle->getDefender();
                } else {
                    $castle = $castlesSchema[$castleId];
                    $defender = Cli_Model_Database::getDefenderFromCastlePosition($user->parameters['gameId'], $castle['position'], $db);
                }
            } else {
                $defender = Cli_Model_Database::getDefenderFromPosition($user->parameters['gameId'], array('x' => $x, 'y' => $y), $db);
            }

            if (empty($defender)) {
                if (Zend_Validate::is($castleId, 'Digits')) {
                    if ($defenderColor == 'neutral') {
                        Cli_Model_Database::addCastle($user->parameters['gameId'], $castleId, $user->parameters['playerId'], $db);
                    } else {
                        Cli_Model_Database::changeOwner($user->parameters['gameId'], $castlesSchema[$castleId], $user->parameters['playerId'], $db);
                    }
                }
                Cli_Model_Database::updateArmyPosition($user->parameters['gameId'], $user->parameters['playerId'], $move['path'], $fields, $army, $db);
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

        $gameHandler->sendToChannel($db, $token, $user->parameters['gameId']);
    }

}