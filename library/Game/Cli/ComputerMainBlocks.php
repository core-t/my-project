<?php

class Game_Cli_ComputerMainBlocks {

    static private function firstBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db = null) {
        if (!Game_Cli_Database::enemiesCastlesExist($gameId, $playerId, $db)) {
            new Game_Logger('BRAK ZAMKÓW WROGA');
            return self::secondBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
        } else {
            new Game_Logger('SĄ ZAMKI WROGA');
            $castleId = Game_Cli_ComputerSubBlocks::getWeakerEnemyCastle($gameId, $castlesAndFields['hostileCastles'], $army, $playerId, $db);
            if ($castleId !== null) {
                new Game_Logger('JEST SŁABSZY ZAMEK WROGA');
                $castleRange = Game_Cli_ComputerSubBlocks::isEnemyCastleInRange($castlesAndFields, $castleId, $army);
                if ($castleRange['in']) {
                    //atakuj
                    new Game_Logger('SŁABSZY ZAMEK WROGA W ZASIĘGU - ATAKUJĘ!');
                    $fightEnemy = Game_Cli_ComputerSubBlocks::fightEnemy($gameId, $army, null, $playerId, $castleId, $db);
                    Game_Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $castleRange['currentPosition'], $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $castleRange['currentPosition'], $castleRange['path'], $fightEnemy, $castleId);
                } else {
                    new Game_Logger('SŁABSZY ZAMEK WROGA POZA ZASIĘGIEM');
                    $enemy = Game_Cli_ComputerSubBlocks::getWeakerEnemyArmyInRange($gameId, $playerId, $enemies, $army, $castlesAndFields, $db);
                    if ($enemy) {
                        //atakuj
                        new Game_Logger('JEST SŁABSZA ARMIA WROGA W ZASIĘGU');
                        $fightEnemy = Game_Cli_ComputerSubBlocks::fightEnemy($gameId, $army, $enemy, $playerId, $enemy['castleId'], $db);
                        Game_Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $enemy['currentPosition'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], $enemy['currentPosition'], $enemy['path'], $fightEnemy, $enemy['castleId']);
                    } else {
                        new Game_Logger('BRAK SŁABSZEJ ARMII WROGA W ZASIĘGU');
                        $enemy = Game_Cli_ComputerSubBlocks::getStrongerEnemyArmyInRange($gameId, $playerId, $enemies, $army, $castlesAndFields, $db);
                        if ($enemy) {
                            new Game_Logger('JEST SILNIEJSZA ARMIA WROGA W ZASIĘGU');
                            $join = Game_Cli_ComputerSubBlocks::getMyArmyInRange($gameId, $army, $castlesAndFields['fields'], $db);
                            if ($join) {
                                new Game_Logger('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                                Game_Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $join['currentPosition'], $db);
                                return self::endMove($playerId, $db, $gameId, $army['armyId'], $join['currentPosition'], $join['path']);
                            } else {
                                new Game_Logger('BRAK MOJEJ ARMII W ZASIĘGU - IDŹ DO ZAMKU!');
                                Game_Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $castleRange['currentPosition'], $db);
                                Game_Cli_Database::zeroArmyMovesLeft($gameId, $army['armyId'], $db);
                                return self::endMove($playerId, $db, $gameId, $army['armyId'], $castleRange['currentPosition'], $castleRange['path']);
                            }
                        } else {
                            new Game_Logger('BRAK SILNIEJSZEJ ARMII WROGA W ZASIĘGU - IDŹ DO ZAMKU!');

                            Game_Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $castleRange['currentPosition'], $db);
                            Game_Cli_Database::zeroArmyMovesLeft($gameId, $army['armyId'], $db);
                            return self::endMove($playerId, $db, $gameId, $army['armyId'], $castleRange['currentPosition'], $castleRange['path']);
                        }
                    }
                }
            } else {
                new Game_Logger('BRAK SŁABSZYCH ZAMKÓW WROGA');
                return self::secondBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
            }
        }
    }

    static private function secondBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db = null) {
        if (!$enemies) {
            throw new Exception('Wygrałem!?');
        } else {
            foreach ($enemies as $e)
            {
                $castleId = Application_Model_Board::isCastleAtPosition($e['x'], $e['y'], $castlesAndFields['hostileCastles']);
                if (null !== $castleId) {
                    continue;
                }
                if (Game_Cli_ComputerSubBlocks::isEnemyStronger($gameId, $playerId, $db, $army, $e, $castleId)) {
                    continue;
                } else {
                    $enemy = $e;
                    break;
                }
            }
            if (isset($enemy)) {
                //atakuj
                new Game_Logger('WRÓG JEST SŁABSZY');
                $range = Game_Cli_ComputerSubBlocks::isEnemyArmyInRange($castlesAndFields, $enemy, $army);
                if ($range['in']) {
                    new Game_Logger('SŁABSZY WRÓG W ZASIĘGU - ATAKUJ!');
                    $fightEnemy = Game_Cli_ComputerSubBlocks::fightEnemy($gameId, $army, $enemy, $playerId, $range['castleId'], $db);
                    Game_Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $range['currentPosition'], $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $range['currentPosition'], $range['path'], $fightEnemy);
                } else {
                    new Game_Logger('SŁABSZY WRÓG POZA ZASIĘGIEM - IDŹ DO WROGA');
                    Game_Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $range['currentPosition'], $db);
                    Game_Cli_Database::zeroArmyMovesLeft($gameId, $army['armyId'], $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $range['currentPosition'], $range['path']);
                }
            } else {
                new Game_Logger('WRÓG JEST SILNIEJSZY');
                $join = Game_Cli_ComputerSubBlocks::getMyArmyInRange($gameId, $army, $castlesAndFields['fields'], $db);
                if ($join) {
                    new Game_Logger('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                    Game_Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $join['currentPosition'], $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $join['currentPosition'], $join['path']);
                } else {
                    new Game_Logger('BRAK MOJEJ ARMII W ZASIĘGU');
                    $castle = Game_Cli_ComputerSubBlocks::getMyCastelNearEnemy($enemies, $army, $castlesAndFields['fields'], $myCastles);
                    if ($castle) {
                        new Game_Logger('JEST MÓJ ZAMEK W POBLIŻU WROGA - IDŹ DO ZAMKU');
                        Game_Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $castle['currentPosition'], $db);
                        Game_Cli_Database::zeroArmyMovesLeft($gameId, $army['armyId'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], $castle['currentPosition'], $castle['path']);
                    } else {
                        new Game_Logger('NIE MA MOJEGO ZAMKU W POBLIŻU WROGA - ZOSTAŃ');
                        Game_Cli_Database::zeroArmyMovesLeft($gameId, $army['armyId'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    }
                }
            }
        }
    }

    static private function ruinBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db = null) {
        if (empty($army['heroes'])) {
            new Game_Logger('BRAK HEROSA');
            return self::firstBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
        } else {
            new Game_Logger('JEST HEROS');
            new Game_Logger($army['heroes'], 'HEROS:');
            $ruin = Game_Cli_ComputerSubBlocks::getNearestRuin($castlesAndFields['fields'], Game_Cli_Database::getFull($gameId, $db), $army);
            if (!$ruin) {
                new Game_Logger('BRAK RUIN');
                return self::firstBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
            } else {
                //idź do ruin
                new Game_Logger('IDŹ DO RUIN');
                Game_Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $ruin['currentPosition'], $db);
                Game_Cli_Database::searchRuin($gameId, $ruin['ruinId'], $army['heroes'][0]['heroId'], $army['armyId'], $playerId, $db);
                return self::endMove($playerId, $db, $gameId, $army['armyId'], $ruin['currentPosition'], $ruin['path'], null, null, $ruin['ruinId']);
            }
        }
    }

    static public function moveArmy($gameId, $playerId, $army, $db = null) {
        new Game_Logger('');
        new Game_Logger($army['armyId'], 'armyId:');

        $canFlySwim = Game_Cli_ComputerSubBlocks::getArmyCanFlySwim($army);
        $army['canFly'] = $canFlySwim['canFly'];
        $army['canSwim'] = $canFlySwim['canSwim'];
        $myCastles = Game_Cli_Database::getPlayerCastles($gameId, $playerId, $db);
        $myCastleId = Application_Model_Board::isCastleAtPosition($army['x'], $army['y'], $myCastles);
        $fields = Game_Cli_Database::getEnemyArmiesFieldsPositions($gameId, $playerId, $db);
        $razed = Game_Cli_Database::getRazedCastles($gameId, $db);
        $castlesAndFields = Application_Model_Board::prepareCastlesAndFields($fields, $razed, $myCastles);
        $enemies = Game_Cli_Database::getAllEnemiesArmies($gameId, $playerId, $db);

        if ($myCastleId !== null) {
            new Game_Logger('W ZAMKU');

            $castlePosition = Application_Model_Board::getCastlePosition($myCastleId);
            $enemiesHaveRange = Game_Cli_ComputerSubBlocks::canEnemyReachThisCastle($castlePosition, $castlesAndFields, $enemies);
            $enemiesInRange = Game_Cli_ComputerSubBlocks::getEnemiesInRange($enemies, $army, $castlesAndFields['fields']);
            if (!$enemiesHaveRange) {
                new Game_Logger('BRAK WROGA Z ZASIĘGIEM');

                if (!$enemiesInRange) {
                    new Game_Logger('BRAK WROGA W ZASIĘGU');

                    return self::ruinBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
                } else {
                    new Game_Logger('JEST WRÓG W ZASIĘGU');

                    foreach ($enemiesInRange as $e)
                    {
                        $castleId = Application_Model_Board::isCastleAtPosition($e['x'], $e['y'], $castlesAndFields['hostileCastles']);
                        if (Game_Cli_ComputerSubBlocks::isEnemyStronger($gameId, $playerId, $db, $army, $e, $castleId)) {
                            continue;
                        } else {
                            $enemy = $e;
                            break;
                        }
                    }
                    if (isset($enemy)) {
                        new Game_Logger('WRÓG JEST SŁABSZY - ATAKUJ!');

                        if ($castleId !== null) {
                            $range = Game_Cli_ComputerSubBlocks::isEnemyCastleInRange($castlesAndFields, $castleId, $army);
                        } else {
                            $range = Game_Cli_ComputerSubBlocks::isEnemyArmyInRange($castlesAndFields, $enemy, $army);
                        }
                        $fightEnemy = Game_Cli_ComputerSubBlocks::fightEnemy($gameId, $army, $enemy, $playerId, $castleId, $db);
                        Game_Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $range['currentPosition'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], $range['currentPosition'], $range['path'], $fightEnemy, $castleId);
                    } else {
                        new Game_Logger('WRÓG JEST SILNIEJSZY - ZOSTAŃ!');

                        Game_Cli_Database::zeroArmyMovesLeft($gameId, $army['armyId'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    }
                }
            } else {
                new Game_Logger('JEST WRÓG Z ZASIĘGIEM');

                if (!$enemiesInRange) {
                    new Game_Logger('BRAK WROGA W ZASIĘGU - ZOSTAŃ!');

                    Game_Cli_Database::zeroArmyMovesLeft($gameId, $army['armyId'], $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                } else {
                    new Game_Logger('JEST WRÓG W ZASIĘGU');

                    if (count($enemiesHaveRange) > count($enemiesInRange)) {
                        new Game_Logger('WRÓGÓW Z ZASIĘGIEM > WRÓGÓW W ZASIĘGU - ZOSTAŃ!');

                        Game_Cli_Database::zeroArmyMovesLeft($gameId, $army['armyId'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    } else {
                        new Game_Logger('WRÓGÓW Z ZASIĘGIEM <= WRÓGÓW W ZASIĘGU');

                        $enemy = Game_Cli_ComputerSubBlocks::canAttackAllEnemyHaveRange($gameId, $playerId, $enemiesHaveRange, $army, $castlesAndFields['hostileCastles'], $db);
                        if (!$enemy) {
                            new Game_Logger('NIE MOGĘ ZAATAKOWAĆ WRÓGÓW Z ZASIĘGIEM - ZOSTAŃ!');

                            Game_Cli_Database::zeroArmyMovesLeft($gameId, $army['armyId'], $db);
                            return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                        } else {
                            //atakuj
                            new Game_Logger('ATAKUJĘ WRÓGÓW Z ZASIĘGIEM - ATAKUJ!'); //atakuję wrogów którzy mają zasięg na zamek, brak enemy armyId, armia nie zmienia pozycji

                            $aStar = $enemy['aStar'];
                            $aStar->getPath($enemy['key'], $enemy['movesToSpend']);
                            $path = $aStar->reversePath();
                            $currentPosition = $aStar->getCurrentPosition();
                            $fightEnemy = Game_Cli_ComputerSubBlocks::fightEnemy($gameId, $army, $enemy, $playerId, $enemy['castleId'], $db);
                            Game_Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $currentPosition, $db);
                            return self::endMove($playerId, $db, $gameId, $army['armyId'], $currentPosition, $path, $fightEnemy, $enemy['castleId']);
                        }
                    }
                }
            }
        } else {
            new Game_Logger('POZA ZAMKIEM');

            $myEmptyCastle = Game_Cli_ComputerSubBlocks::getMyEmptyCastleInMyRange($gameId, $myCastles, $army, $castlesAndFields['fields'], $db);
            if (!$myEmptyCastle) {
                new Game_Logger('NIE MA MOJEGO PUSTEGO ZAMKU W ZASIĘGU');

                return self::ruinBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
            } else {
                new Game_Logger('JEST MÓJ PUSTY ZAMEK W ZASIĘGU');

                if (!Game_Cli_ComputerSubBlocks::isMyCastleInRangeOfEnemy($enemies, $myEmptyCastle, $castlesAndFields['fields'])) {
                    new Game_Logger('WRÓG NIE MA ZASIĘGU NA PUSTY ZAMEK');

                    return self::firstBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
                } else {
                    //idź do zamku
                    new Game_Logger('WRÓG MA ZASIĘG NA PUSTY ZAMEK - IDŹ DO ZAMKU!');

                    $data = array(
                        'x' => $myEmptyCastle['x'],
                        'y' => $myEmptyCastle['y'],
                        'movesSpend' => $army['movesLeft']
                    );
                    Game_Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $data, $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $myEmptyCastle['currentPosition'], $myEmptyCastle['path']);
                }
            }
        }
    }

    static private function endMove($playerId, $db, $gameId, $oldArmyId, $position, $path = null, $fightEnemy = null, $castleId = null, $ruinId = null) {

        $armiesIds = Game_Cli_Database::joinArmiesAtPosition($gameId, $position, $playerId, $db);
        $armyId = $armiesIds['armyId'];

        if (!$armyId) {
            $armyId = $oldArmyId;
        }

        if ($fightEnemy) {
            $attackerArmy = $fightEnemy['attackerArmy'];
            $attackerArmy['x'] = $position['x'];
            $attackerArmy['y'] = $position['y'];
            $defenderArmy = $fightEnemy['defenderArmy'];
        } else {
            $attackerArmy = Game_Cli_Database::getArmyByArmyIdPlayerId($gameId, $armyId, $playerId, $db);
            $defenderArmy = null;
        }

//        print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4));

        return array(
            'defenderColor' => $fightEnemy['defenderColor'],
            'defenderArmy' => $defenderArmy,
            'attackerColor' => Game_Cli_Database::getPlayerColor($gameId, $playerId, $db),
            'attackerArmy' => $attackerArmy,
            'battle' => $fightEnemy['battle'],
            'victory' => $fightEnemy['victory'],
            'path' => $path,
            'castleId' => $castleId,
            'ruinId' => $ruinId,
            'deletedIds' => $armiesIds['deletedIds'],
            'oldArmyId' => $oldArmyId,
            'action' => 'continue'
        );
    }

//    static public function endTurn($gameId, $playerId, $db = null) {
//        $youWin = false;
//        $response = array();
//        $nextPlayer = array(
//            'color' => Game_Cli_Database::getPlayerColor($gameId, $playerId, $db)
//        );
//        while (empty($response))
//        {
//            $nextPlayer = Game_Cli_Database::getExpectedNextTurnPlayer($gameId, $nextPlayer['color'], $db);
//            $playerCastlesExists = Game_Cli_Database::playerCastlesExists($gameId, $nextPlayer['playerId'], $db);
//            $playerArmiesExists = Game_Cli_Database::playerArmiesExists($gameId, $nextPlayer['playerId'], $db);
//            if ($playerCastlesExists || $playerArmiesExists) {
//                $response = $nextPlayer;
//                if ($nextPlayer['playerId'] == $playerId) {
//                    $youWin = true;
//                    Game_Cli_Database::endGame($gameId, $db);
//                } else {
//                    $nr = Game_Cli_Database::updateTurnNumber($gameId, $nextPlayer['playerId'], $db);
//                    if ($nr) {
//                        $response['nr'] = $nr;
//                    }
//                    Game_Cli_Database::raiseAllCastlesProductionTurn($gameId, $playerId, $db);
//                }
//                $response['win'] = $youWin;
//            } else {
//                Game_Cli_Database::setPlayerLostGame($gameId, $nextPlayer['playerId'], $db);
//            }
//        }
//        $response['action'] = 'end';
//        unset($response['playerId']);
//
//        return $response;
//    }

    static public function startTurn($gameId, $playerId, $db = null) {
        Game_Cli_Database::turnActivate($gameId, $playerId, $db);
        Game_Cli_Database::resetHeroesMovesLeft($gameId, $playerId, $db);
        Game_Cli_Database::resetSoldiersMovesLeft($gameId, $playerId, $db);

        $gold = Game_Cli_Database::getPlayerInGameGold($gameId, $playerId, $db);
        $income = 0;
        $costs = 0;
        $castles = array();
        $color = null;
        $turnNumber = Game_Cli_Database::getTurnNumber($gameId, $db);

        if ($turnNumber == 0) {
            var_dump('?');
            return;
        }

        $castlesId = Game_Cli_Database::getPlayerCastles($gameId, $playerId, $db);
        foreach ($castlesId as $id)
        {
            $castleId = $id['castleId'];
            $castles[$castleId] = Application_Model_Board::getCastle($castleId);
            $castle = $castles[$castleId];
            $income += $castle['income'];
            $castleProduction = Game_Cli_Database::getCastleProduction($gameId, $castleId, $playerId, $db);
            if ($turnNumber < 10) {
                $unitName = Application_Model_Board::getMinProductionTimeUnit($castleId);
            } else {
                $unitName = Application_Model_Board::getCastleOptimalProduction($castleId);
            }
            $unitId = Game_Cli_Database::getUnitIdByName($unitName, $db);
            if ($unitId != $castleProduction['production']) {
                Game_Cli_Database::setCastleProduction($gameId, $castleId, $unitId, $playerId, $db);
                $castleProduction = Game_Cli_Database::getCastleProduction($gameId, $castleId, $playerId, $db);
            }
            $castles[$castleId]['productionTurn'] = $castleProduction['productionTurn'];
            $unitName = Application_Model_Board::getUnitName($castleProduction['production']);
            if ($castle['production'][$unitName]['time'] <= $castleProduction['productionTurn'] AND $castle['production'][$unitName]['cost'] <= $gold) {
                if (Game_Cli_Database::resetProductionTurn($gameId, $castleId, $playerId, $db) == 1) {
                    $armyId = Game_Cli_Database::getArmyIdFromPosition($gameId, $castle['position'], $db);
                    if (!$armyId) {
                        $armyId = Game_Cli_Database::createArmy($gameId, $castle['position'], $playerId, $db);
                    }
                    Game_Cli_Database::addSoldierToArmy($gameId, $armyId, $castleProduction['production'], $db);
                }
            }
        }
        if (isset($castle['position'])) {
            $gold = self::handleHeroResurrection($gameId, $gold, $castle['position'], $playerId, $db);
        }

        $armies = Game_Cli_Database::getPlayerArmies($gameId, $playerId, $db);

        if (empty($castles) && empty($armies)) {
            $action = 'gameover';
        } else {
            foreach ($armies as $army)
            {
                foreach ($army['soldiers'] as $unit)
                {
                    $costs += $unit['cost'];
                }
            }
            $gold = $gold + $income - $costs;
            Game_Cli_Database::updatePlayerInGameGold($gameId, $playerId, $gold, $db);
            $action = 'start';
            $color = Game_Cli_Database::getPlayerColor($gameId, $playerId, $db);
        }

        return array(
            'action' => $action,
            'armies' => $armies,
            'color' => $color
        );
    }

    static private function handleHeroResurrection($gameId, $gold, $position, $playerId, $db = null) {
        if (!Game_Cli_Database::isHeroInGame($gameId, $playerId, $db)) {
            Game_Cli_Database::connectHero($gameId, $playerId, $db);
        }
        $heroId = Game_Cli_Database::getDeadHeroId($gameId, $playerId, $db);
        if ($heroId) {
            if ($gold >= 100) {
                $armyId = Game_Cli_Database::heroResurection($gameId, $heroId, $position, $playerId, $db);
                if ($armyId) {
                    return $gold - 100;
                }
            }
        }
        return $gold;
    }

}

