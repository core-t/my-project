<?php

class Cli_Model_ComputerMainBlocks
{

    static private function firstBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db)
    {
        $army = $mArmy->getArmy();
        if (!Cli_Model_Database::enemiesCastlesExist($gameId, $playerId, $db)) {
            new Cli_Model_Logger('BRAK ZAMKÓW WROGA');
            return self::secondBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db);
        } else {
            new Cli_Model_Logger('SĄ ZAMKI WROGA');
            $castleId = Cli_Model_ComputerSubBlocks::getWeakerEnemyCastle($gameId, $castlesAndFields['hostileCastles'], $army, $playerId, $db);
            if ($castleId !== null) {
                new Cli_Model_Logger('JEST SŁABSZY ZAMEK WROGA');
                $castleRange = Cli_Model_ComputerSubBlocks::isEnemyCastleInRange($castlesAndFields, $castleId, $mArmy);
                if ($castleRange['in']) {
                    //atakuj
                    new Cli_Model_Logger('SŁABSZY ZAMEK WROGA W ZASIĘGU - ATAKUJĘ!');
                    $fightEnemy = Cli_Model_ComputerSubBlocks::fightEnemy($gameId, $army, $castleRange['path'], $castlesAndFields['fields'], null, $playerId, $castleId, $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $castleRange['currentPosition'], $castleRange['path'], $fightEnemy, $castleId);
                } else {
                    new Cli_Model_Logger('SŁABSZY ZAMEK WROGA POZA ZASIĘGIEM');
                    $enemy = Cli_Model_ComputerSubBlocks::getWeakerEnemyArmyInRange($gameId, $playerId, $enemies, $army, $castlesAndFields, $db);
                    if ($enemy) {
                        //atakuj
                        new Cli_Model_Logger('JEST SŁABSZA ARMIA WROGA W ZASIĘGU');
                        $fightEnemy = Cli_Model_ComputerSubBlocks::fightEnemy($gameId, $army, $enemy['path'], $castlesAndFields['fields'], $enemy, $playerId, $enemy['castleId'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], $enemy['currentPosition'], $enemy['path'], $fightEnemy, $enemy['castleId']);
                    } else {
                        new Cli_Model_Logger('BRAK SŁABSZEJ ARMII WROGA W ZASIĘGU');
                        $enemy = Cli_Model_ComputerSubBlocks::getStrongerEnemyArmyInRange($gameId, $playerId, $enemies, $army, $castlesAndFields, $db);
                        if ($enemy) {
                            new Cli_Model_Logger('JEST SILNIEJSZA ARMIA WROGA W ZASIĘGU');
                            $join = Cli_Model_ComputerSubBlocks::getMyArmyInRange($gameId, $playerId, $army, $castlesAndFields['fields'], $db);
                            if ($join) {
                                new Cli_Model_Logger('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                                Cli_Model_Database::updateArmyPosition($gameId, $playerId, $join['path'], $castlesAndFields['fields'], $army, $db);
                                return self::endMove($playerId, $db, $gameId, $army['armyId'], $join['currentPosition'], $join['path']);
                            } else {
                                new Cli_Model_Logger('BRAK MOJEJ ARMII W ZASIĘGU - IDŹ DO ZAMKU!');
                                Cli_Model_Database::updateArmyPosition($gameId, $playerId, $castleRange['path'], $castlesAndFields['fields'], $army, $db);
                                Cli_Model_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                                return self::endMove($playerId, $db, $gameId, $army['armyId'], $castleRange['currentPosition'], $castleRange['path']);
                            }
                        } else {
                            new Cli_Model_Logger('BRAK SILNIEJSZEJ ARMII WROGA W ZASIĘGU - IDŹ DO ZAMKU!');

                            Cli_Model_Database::updateArmyPosition($gameId, $playerId, $castleRange['path'], $castlesAndFields['fields'], $army, $db);
                            Cli_Model_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                            return self::endMove($playerId, $db, $gameId, $army['armyId'], $castleRange['currentPosition'], $castleRange['path']);
                        }
                    }
                }
            } else {
                new Cli_Model_Logger('BRAK SŁABSZYCH ZAMKÓW WROGA');
                return self::secondBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db);
            }
        }
    }

    static private function secondBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db)
    {
        $army = $mArmy->getArmy();
        if (!$enemies) {
            throw new Exception('Wygrałem!?');
        } else {
            foreach ($enemies as $e) {
                $castleId = Application_Model_Board::isCastleAtPosition($e['x'], $e['y'], $castlesAndFields['hostileCastles']);
                if (null !== $castleId) {
                    continue;
                }
                if (Cli_Model_ComputerSubBlocks::isEnemyStronger($gameId, $playerId, $db, $army, $e, $castleId)) {
                    continue;
                } else {
                    $enemy = $e;
                    break;
                }
            }
            if (isset($enemy)) {
                //atakuj
                new Cli_Model_Logger('WRÓG JEST SŁABSZY');
                $range = Cli_Model_ComputerSubBlocks::isEnemyArmyInRange($castlesAndFields, $enemy, $mArmy);
                if ($range['in']) {
                    new Cli_Model_Logger('SŁABSZY WRÓG W ZASIĘGU - ATAKUJ!');
                    $fightEnemy = Cli_Model_ComputerSubBlocks::fightEnemy($gameId, $army, $range['path'], $castlesAndFields['fields'], $enemy, $playerId, $range['castleId'], $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $range['currentPosition'], $range['path'], $fightEnemy);
                } else {
                    new Cli_Model_Logger('SŁABSZY WRÓG POZA ZASIĘGIEM - IDŹ DO WROGA');
                    Cli_Model_Database::updateArmyPosition($gameId, $playerId, $range['path'], $castlesAndFields['fields'], $army, $db);
                    Cli_Model_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $range['currentPosition'], $range['path']);
                }
            } else {
                new Cli_Model_Logger('WRÓG JEST SILNIEJSZY');
                $join = Cli_Model_ComputerSubBlocks::getMyArmyInRange($gameId, $playerId, $army, $castlesAndFields['fields'], $db);
                if ($join) {
                    new Cli_Model_Logger('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                    Cli_Model_Database::updateArmyPosition($gameId, $playerId, $join['path'], $castlesAndFields['fields'], $army, $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $join['currentPosition'], $join['path']);
                } else {
                    new Cli_Model_Logger('BRAK MOJEJ ARMII W ZASIĘGU');
                    $castle = Cli_Model_ComputerSubBlocks::getMyCastleNearEnemy($enemies, $army, $castlesAndFields['fields'], $myCastles);
                    if ($castle) {
                        new Cli_Model_Logger('JEST MÓJ ZAMEK W POBLIŻU WROGA - IDŹ DO ZAMKU');
                        Cli_Model_Database::updateArmyPosition($gameId, $playerId, $castle['path'], $castlesAndFields['fields'], $army, $db);
                        Cli_Model_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], $castle['currentPosition'], $castle['path']);
                    } else {
                        new Cli_Model_Logger('NIE MA MOJEGO ZAMKU W POBLIŻU WROGA - ZOSTAŃ');
                        Cli_Model_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    }
                }
            }
        }
    }

    static private function ruinBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db)
    {
        $army = $mArmy->getArmy();
        if (empty($army['heroes'])) {
            new Cli_Model_Logger('BRAK HEROSA');
            return self::firstBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db);
        } else {
            new Cli_Model_Logger('JEST HEROS');
//            new Cli_Model_Logger($army['heroes'], 'HEROS:');
            $ruin = Cli_Model_ComputerSubBlocks::getNearestRuin($castlesAndFields['fields'], Cli_Model_Database::getFullRuins($gameId, $db), $army);
            if (!$ruin) {
                new Cli_Model_Logger('BRAK RUIN');
                return self::firstBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db);
            } else {
                //idź do ruin
                new Cli_Model_Logger('IDŹ DO RUIN');
                Cli_Model_Database::updateArmyPosition($gameId, $playerId, $ruin['path'], $castlesAndFields['fields'], $army, $db);
                Cli_Model_SearchRuin::search($gameId, $ruin['ruinId'], $army['heroes'][0]['heroId'], $army['armyId'], $playerId, $db);
                Cli_Model_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                return self::endMove($playerId, $db, $gameId, $army['armyId'], $ruin['currentPosition'], $ruin['path'], null, null, $ruin['ruinId']);
            }
        }
    }

    static public function moveArmy($gameId, $playerId, $mArmy, $db)
    {
        $army = $mArmy->getArmy();
        new Cli_Model_Logger('');
        new Cli_Model_Logger($army['armyId'], 'armyId:');

        $myCastles = Cli_Model_Database::getPlayerCastles($gameId, $playerId, $db);
        $myCastleId = Application_Model_Board::isCastleAtPosition($army['x'], $army['y'], $myCastles);
        $fields = Cli_Model_Database::getEnemyArmiesFieldsPositions($gameId, $playerId, $db);
        $razed = Cli_Model_Database::getRazedCastles($gameId, $db);
        $castlesAndFields = Application_Model_Board::prepareCastlesAndFields($fields, $razed, $myCastles);
        $enemies = Cli_Model_Database::getAllEnemiesArmies($gameId, $playerId, $db);

        if ($myCastleId !== null) {
            new Cli_Model_Logger('W ZAMKU');

            $castlePosition = Application_Model_Board::getCastlePosition($myCastleId);
            $enemiesHaveRange = Cli_Model_ComputerSubBlocks::getEnemiesHaveRangeAtThisCastle($castlePosition, $castlesAndFields, $enemies);
            $enemiesInRange = Cli_Model_ComputerSubBlocks::getEnemiesInRange($enemies, $mArmy, $castlesAndFields['fields']);
            if (!$enemiesHaveRange) {
                new Cli_Model_Logger('BRAK WROGA Z ZASIĘGIEM');

                if (!$enemiesInRange) {
                    new Cli_Model_Logger('BRAK WROGA W ZASIĘGU');

                    return self::ruinBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
                } else {
                    new Cli_Model_Logger('JEST WRÓG W ZASIĘGU');

                    foreach ($enemiesInRange as $e) {
                        $castleId = Application_Model_Board::isCastleAtPosition($e['x'], $e['y'], $castlesAndFields['hostileCastles']);
                        if (Cli_Model_ComputerSubBlocks::isEnemyStronger($gameId, $playerId, $db, $army, $e, $castleId)) {
                            continue;
                        } else {
                            $enemy = $e;
                            break;
                        }
                    }
                    if (isset($enemy)) {
                        new Cli_Model_Logger('WRÓG JEST SŁABSZY - ATAKUJ!');

                        if ($castleId !== null) {
                            $range = Cli_Model_ComputerSubBlocks::isEnemyCastleInRange($castlesAndFields, $castleId, $mArmy);
                        } else {
                            $range = Cli_Model_ComputerSubBlocks::isEnemyArmyInRange($castlesAndFields, $enemy, $mArmy);
                        }
                        $fightEnemy = Cli_Model_ComputerSubBlocks::fightEnemy($gameId, $army, $range['path'], $castlesAndFields['fields'], $enemy, $playerId, $castleId, $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], $range['currentPosition'], $range['path'], $fightEnemy, $castleId);
                    } else {
                        new Cli_Model_Logger('WRÓG JEST SILNIEJSZY - ZOSTAŃ!');

                        Cli_Model_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    }
                }
            } else {
                new Cli_Model_Logger('JEST WRÓG Z ZASIĘGIEM');

                if (!$enemiesInRange) {
                    new Cli_Model_Logger('BRAK WROGA W ZASIĘGU - ZOSTAŃ!');

                    Cli_Model_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                } else {
                    new Cli_Model_Logger('JEST WRÓG W ZASIĘGU');

                    if (count($enemiesHaveRange) > count($enemiesInRange)) {
                        new Cli_Model_Logger('WRÓGÓW Z ZASIĘGIEM > WRÓGÓW W ZASIĘGU - ZOSTAŃ!');

                        Cli_Model_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    } else {
                        new Cli_Model_Logger('WRÓGÓW Z ZASIĘGIEM <= WRÓGÓW W ZASIĘGU');

                        $enemy = Cli_Model_ComputerSubBlocks::canAttackAllEnemyHaveRange($gameId, $playerId, $enemiesHaveRange, $army, $castlesAndFields['hostileCastles'], $db);
                        if (!$enemy) {
                            new Cli_Model_Logger('NIE MOGĘ ZAATAKOWAĆ WRÓGÓW Z ZASIĘGIEM - ZOSTAŃ!');

                            Cli_Model_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                            return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                        } else {
                            //atakuj
                            new Cli_Model_Logger('ATAKUJĘ WRÓGÓW Z ZASIĘGIEM - ATAKUJ!'); //atakuję wrogów którzy mają zasięg na zamek, brak enemy armyId, armia nie zmienia pozycji

                            $aStar = $enemy['aStar'];
                            $aStar->getPath($enemy['key'], $enemy['movesToSpend']);
                            $path = $aStar->reversePath();
                            $currentPosition = $aStar->getCurrentPosition();
                            $fightEnemy = Cli_Model_ComputerSubBlocks::fightEnemy($gameId, $army, $path, $castlesAndFields['fields'], $enemy, $playerId, $enemy['castleId'], $db);
                            return self::endMove($playerId, $db, $gameId, $army['armyId'], $currentPosition, $path, $fightEnemy, $enemy['castleId']);
                        }
                    }
                }
            }
        } else {
            new Cli_Model_Logger('POZA ZAMKIEM');

            $myEmptyCastle = Cli_Model_ComputerSubBlocks::getMyEmptyCastleInMyRange($gameId, $myCastles, $army, $castlesAndFields['fields'], $db);
            if (!$myEmptyCastle) {
                new Cli_Model_Logger('NIE MA MOJEGO PUSTEGO ZAMKU W ZASIĘGU');

                return self::ruinBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
            } else {
                new Cli_Model_Logger('JEST MÓJ PUSTY ZAMEK W ZASIĘGU');

                if (!Cli_Model_ComputerSubBlocks::isMyCastleInRangeOfEnemy($enemies, $myEmptyCastle, $castlesAndFields['fields'])) {
                    new Cli_Model_Logger('WRÓG NIE MA ZASIĘGU NA PUSTY ZAMEK');

                    return self::firstBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db);
                } else {
                    //idź do zamku
                    new Cli_Model_Logger('WRÓG MA ZASIĘG NA PUSTY ZAMEK - IDŹ DO ZAMKU!');

                    Cli_Model_Database::updateArmyPosition($gameId, $playerId, $myEmptyCastle['path'], $castlesAndFields['fields'], $army, $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $myEmptyCastle['currentPosition'], $myEmptyCastle['path']);
                }
            }
        }
    }

    static private function endMove($playerId, $db, $gameId, $oldArmyId, $position, $path = null, $fightEnemy = null, $castleId = null, $ruinId = null)
    {

        $armiesIds = Cli_Model_Database::joinArmiesAtPosition($gameId, $position, $playerId, $db);
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
            $attackerArmy = Cli_Model_Database::getArmyByArmyIdPlayerId($gameId, $armyId, $playerId, $db);
            $defenderArmy = null;
        }

//        print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4));

        return array(
            'defenderColor' => $fightEnemy['defenderColor'],
            'defenderArmy' => $defenderArmy,
            'attackerColor' => Cli_Model_Database::getColorByPlayerId($gameId, $playerId, $db),
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

    static public function startTurn($gameId, $playerId, $db = null)
    {
        Cli_Model_Database::turnActivate($gameId, $playerId, $db);
        Cli_Model_Database::unfortifyComputerArmies($gameId, $playerId, $db);
        Cli_Model_Database::resetHeroesMovesLeft($gameId, $playerId, $db);
        Cli_Model_Database::resetSoldiersMovesLeft($gameId, $playerId, $db);

        $gold = Cli_Model_Database::getPlayerInGameGold($gameId, $playerId, $db);
        $income = 0;
        $castles = array();
        $color = null;
        $turnNumber = Cli_Model_Database::getTurnNumber($gameId, $db);

        if ($turnNumber == 0) {
            var_dump('?');
            return;
        }

        $castlesId = Cli_Model_Database::getPlayerCastlesIds($gameId, $playerId, $db);
        foreach ($castlesId as $id) {
            $castleId = $id['castleId'];
            $castles[$castleId] = Application_Model_Board::getCastle($castleId);
            $castle = $castles[$castleId];
            $income += $castle['income'];
            $castleProduction = Cli_Model_Database::getCastleProduction($gameId, $castleId, $playerId, $db);
            if ($turnNumber < 10) {
                $unitName = Application_Model_Board::getMinProductionTimeUnit($castleId);
            } else {
                $unitName = Application_Model_Board::getCastleOptimalProduction($castleId);
            }
            $unitId = Cli_Model_Database::getUnitIdByName($unitName, $db);
            if ($unitId != $castleProduction['production']) {
                Cli_Model_Database::setCastleProduction($gameId, $castleId, $unitId, $playerId, $db);
                $castleProduction = Cli_Model_Database::getCastleProduction($gameId, $castleId, $playerId, $db);
            }
            $castles[$castleId]['productionTurn'] = $castleProduction['productionTurn'];
            $unitName = Application_Model_Board::getUnitName($castleProduction['production']);
            if ($castle['production'][$unitName]['time'] <= $castleProduction['productionTurn'] AND $castle['production'][$unitName]['cost'] <= $gold) {
                if (Cli_Model_Database::resetProductionTurn($gameId, $castleId, $playerId, $db) == 1) {
                    $armyId = Cli_Model_Database::getArmyIdFromPosition($gameId, $castle['position'], $db);
                    if (!$armyId) {
                        $armyId = Cli_Model_Database::createArmy($gameId, $db, $castle['position'], $playerId);
                    }
                    Cli_Model_Database::addSoldierToArmy($gameId, $armyId, $castleProduction['production'], $db);
                }
            }
        }
        if (isset($castle['position'])) {
            $gold = self::handleHeroResurrection($gameId, $gold, $castle['position'], $playerId, $db);
        }

        $armies = Cli_Model_Database::getPlayerArmies($gameId, $playerId, $db);

        if (empty($castles) && empty($armies)) {
            $action = 'gameover';
        } else {
            $income += Cli_Model_Database::calculateIncomeFromTowers($gameId, $playerId, $db);
            $gold = $gold + $income - Cli_Model_Database::calculateCostsOfSoldiers($gameId, $playerId, $db);
            Cli_Model_Database::updatePlayerInGameGold($gameId, $playerId, $gold, $db);
            $action = 'start';
            $color = Cli_Model_Database::getColorByPlayerId($gameId, $playerId, $db);
        }

        return array(
            'action' => $action,
            'armies' => $armies,
            'color' => $color
        );
    }

    static private function handleHeroResurrection($gameId, $gold, $position, $playerId, $db = null)
    {
        if (!Cli_Model_Database::isHeroInGame($gameId, $playerId, $db)) {
            Cli_Model_Database::connectHero($gameId, $playerId, $db);
        }
        $heroId = Cli_Model_Database::getDeadHeroId($gameId, $playerId, $db);
        if ($heroId) {
            if ($gold >= 100) {
                $armyId = Cli_Model_Database::heroResurection($gameId, $heroId, $position, $playerId, $db);
                if ($armyId) {
                    return $gold - 100;
                }
            }
        }
        return $gold;
    }

}

