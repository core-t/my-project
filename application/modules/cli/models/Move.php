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
        $mArmy2 = new Application_Model_Army($user->parameters['gameId'], $db);

        if ($fields[$army['y']][$army['x']] == 'w') {
            if ($army['canSwim'] || $army['canFly']) {
                $otherArmyId = $mArmy2->isOtherArmyAtPosition($attackerArmyId, $army['x'], $army['y']);
                if ($otherArmyId) {
                    $otherArmy = Cli_Model_Database::getArmy($user->parameters['gameId'], $otherArmyId, $user->parameters['playerId'], $db);
                    $mOtherArmy = new Cli_Model_Army($otherArmy);
                    if (!$mOtherArmy->canSwim() && !$mOtherArmy->canFly()) {
                        new Cli_Model_JoinArmy($otherArmyId, $user, $db, $gameHandler);
                        $gameHandler->sendError($user, 'Nie możesz zostawić armii na wodzie.');
                        return;
                    }
                }
            }
        } elseif ($fields[$army['y']][$army['x']] == 'M') {
            $otherArmyId = $mArmy2->isOtherArmyAtPosition($attackerArmyId, $army['x'], $army['y']);
            if ($otherArmyId) {
                $otherArmy = Cli_Model_Database::getArmy($user->parameters['gameId'], $otherArmyId, $user->parameters['playerId'], $db);
                $mOtherArmy = new Cli_Model_Army($otherArmy);
                if (!$mOtherArmy->canFly()) {
                    new Cli_Model_JoinArmy($otherArmyId, $user, $db, $gameHandler);
                    $gameHandler->sendError($user, 'Nie możesz zostawić armii w górach.');
                    return;
                }
            }
        }

        $castlesSchema = Zend_Registry::get('castles');
        $mCastlesInGame = new Application_Model_CastlesInGame($user->parameters['gameId'], $db);
        $allCastles = $mCastlesInGame->getAllCastles();
        $myCastles = array();
        foreach ($allCastles as $castle) {
            if ($castle['playerId'] == $user->parameters['playerId']) {
                $castle['position'] = $castlesSchema[$castle['castleId']]['position'];
                $myCastles[] = $castle;
            }
        }


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
            $A_Star = new Cli_Model_Astar($army, $x, $y, $fields, array('myCastles' => $myCastles));
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
                $defenderColor = $mCastlesInGame->getColorByCastleId($castleId);
                $enemy = Cli_Model_Database::getAllEnemyUnitsFromCastlePosition($user->parameters['gameId'], $castlesSchema[$castleId]['position'], $db);
                $enemy = Cli_Model_Army::addCastleDefenseModifier($enemy, $user->parameters['gameId'], $castleId, $db);
            }
        } elseif ($move['currentPosition']['x'] == $x && $move['currentPosition']['y'] == $y && $enemy['ids']) { // enemy army
            $fight = true;
            $defenderColor = $mArmy2->getColorByArmyId($enemy['ids'][0]);
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
            $battle->updateArmies($user->parameters['gameId'], $db, $user->parameters['playerId'], 0);

            if (Zend_Validate::is($castleId, 'Digits')) {
                if ($defenderColor == 'neutral') {
                    $defender = $battle->getDefender();
                } else {
                    $defender = $mArmy2->getDefender($enemy['ids']);
                }
            } else {
                $defender = $mArmy2->getDefender($enemy['ids']);
            }

            if (!$battle->getDefender()) {
                if (Zend_Validate::is($castleId, 'Digits')) {

                    if ($defenderColor == 'neutral') {
                        $mCastlesInGame->addCastle($castleId, $user->parameters['playerId']);
                    } else {
                        $mCastlesInGame->changeOwner($castlesSchema[$castleId], $user->parameters['playerId']);
                    }
                }
                $mArmy2->updateArmyPosition($user->parameters['playerId'], $move['path'], $fields, $army);
                $attacker = Cli_Model_Database::getArmyByArmyIdPlayerId($user->parameters['gameId'], $attackerArmyId, $user->parameters['playerId'], $db);
                $victory = true;
//                foreach ($enemy['ids'] as $id) {
//                    $defender[]['armyId'] = $id;
//                }
            } else {
                $mArmy2->destroyArmy($army['armyId'], $user->parameters['playerId']);
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
            $mArmy2->updateArmyPosition($user->parameters['playerId'], $move['path'], $fields, $army);
            $armiesIds = $mArmy2->joinArmiesAtPosition($move['currentPosition'], $user->parameters['playerId']);
            $newArmyId = $armiesIds['armyId'];
            $attacker = Cli_Model_Database::getArmyByArmyIdPlayerId($user->parameters['gameId'], $newArmyId, $user->parameters['playerId'], $db);
            $deletedIds = $armiesIds['deletedIds'];
        }

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => 'move',
            'attackerColor' => $playersInGameColors[$user->parameters['playerId']],
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