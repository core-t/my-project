<?php

class Cli_ComputerMainBlocks {

    static private function firstBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db = null) {
        if (!Cli_Database::enemiesCastlesExist($gameId, $playerId, $db)) {
            new Cli_Logger('BRAK ZAMKÓW WROGA');
            return self::secondBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
        } else {
            new Cli_Logger('SĄ ZAMKI WROGA');
            $castleId = Cli_ComputerSubBlocks::getWeakerEnemyCastle($gameId, $castlesAndFields['hostileCastles'], $army, $playerId, $db);
            if ($castleId !== null) {
                new Cli_Logger('JEST SŁABSZY ZAMEK WROGA');
                $castleRange = Cli_ComputerSubBlocks::isEnemyCastleInRange($castlesAndFields, $castleId, $army);
                if ($castleRange['in']) {
                    //atakuj
                    new Cli_Logger('SŁABSZY ZAMEK WROGA W ZASIĘGU - ATAKUJĘ!');
                    $fightEnemy = Cli_ComputerSubBlocks::fightEnemy($gameId, $army, null, $playerId, $castleId, $db);
                    Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $castleRange['currentPosition'], $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $castleRange['currentPosition'], $castleRange['path'], $fightEnemy, $castleId);
                } else {
                    new Cli_Logger('SŁABSZY ZAMEK WROGA POZA ZASIĘGIEM');
                    $enemy = Cli_ComputerSubBlocks::getWeakerEnemyArmyInRange($gameId, $playerId, $enemies, $army, $castlesAndFields, $db);
                    if ($enemy) {
                        //atakuj
                        new Cli_Logger('JEST SŁABSZA ARMIA WROGA W ZASIĘGU');
                        $fightEnemy = Cli_ComputerSubBlocks::fightEnemy($gameId, $army, $enemy, $playerId, $enemy['castleId'], $db);
                        Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $enemy['currentPosition'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], $enemy['currentPosition'], $enemy['path'], $fightEnemy, $enemy['castleId']);
                    } else {
                        new Cli_Logger('BRAK SŁABSZEJ ARMII WROGA W ZASIĘGU');
                        $enemy = Cli_ComputerSubBlocks::getStrongerEnemyArmyInRange($gameId, $playerId, $enemies, $army, $castlesAndFields, $db);
                        if ($enemy) {
                            new Cli_Logger('JEST SILNIEJSZA ARMIA WROGA W ZASIĘGU');
                            $join = Cli_ComputerSubBlocks::getMyArmyInRange($gameId, $playerId, $army, $castlesAndFields['fields'], $db);
                            if ($join) {
                                new Cli_Logger('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                                Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $join['currentPosition'], $db);
                                return self::endMove($playerId, $db, $gameId, $army['armyId'], $join['currentPosition'], $join['path']);
                            } else {
                                new Cli_Logger('BRAK MOJEJ ARMII W ZASIĘGU - IDŹ DO ZAMKU!');
                                Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $castleRange['currentPosition'], $db);
                                Cli_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                                return self::endMove($playerId, $db, $gameId, $army['armyId'], $castleRange['currentPosition'], $castleRange['path']);
                            }
                        } else {
                            new Cli_Logger('BRAK SILNIEJSZEJ ARMII WROGA W ZASIĘGU - IDŹ DO ZAMKU!');

                            Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $castleRange['currentPosition'], $db);
                            Cli_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                            return self::endMove($playerId, $db, $gameId, $army['armyId'], $castleRange['currentPosition'], $castleRange['path']);
                        }
                    }
                }
            } else {
                new Cli_Logger('BRAK SŁABSZYCH ZAMKÓW WROGA');
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
                if (Cli_ComputerSubBlocks::isEnemyStronger($gameId, $playerId, $db, $army, $e, $castleId)) {
                    continue;
                } else {
                    $enemy = $e;
                    break;
                }
            }
            if (isset($enemy)) {
                //atakuj
                new Cli_Logger('WRÓG JEST SŁABSZY');
                $range = Cli_ComputerSubBlocks::isEnemyArmyInRange($castlesAndFields, $enemy, $army);
                if ($range['in']) {
                    new Cli_Logger('SŁABSZY WRÓG W ZASIĘGU - ATAKUJ!');
                    $fightEnemy = Cli_ComputerSubBlocks::fightEnemy($gameId, $army, $enemy, $playerId, $range['castleId'], $db);
                    Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $range['currentPosition'], $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $range['currentPosition'], $range['path'], $fightEnemy);
                } else {
                    new Cli_Logger('SŁABSZY WRÓG POZA ZASIĘGIEM - IDŹ DO WROGA');
                    Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $range['currentPosition'], $db);
                    Cli_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $range['currentPosition'], $range['path']);
                }
            } else {
                new Cli_Logger('WRÓG JEST SILNIEJSZY');
                $join = Cli_ComputerSubBlocks::getMyArmyInRange($gameId, $playerId, $army, $castlesAndFields['fields'], $db);
                if ($join) {
                    new Cli_Logger('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                    Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $join['currentPosition'], $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $join['currentPosition'], $join['path']);
                } else {
                    new Cli_Logger('BRAK MOJEJ ARMII W ZASIĘGU');
                    $castle = Cli_ComputerSubBlocks::getMyCastelNearEnemy($enemies, $army, $castlesAndFields['fields'], $myCastles);
                    if ($castle) {
                        new Cli_Logger('JEST MÓJ ZAMEK W POBLIŻU WROGA - IDŹ DO ZAMKU');
                        Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $castle['currentPosition'], $db);
                        Cli_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], $castle['currentPosition'], $castle['path']);
                    } else {
                        new Cli_Logger('NIE MA MOJEGO ZAMKU W POBLIŻU WROGA - ZOSTAŃ');
                        Cli_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    }
                }
            }
        }
    }

    static private function ruinBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db = null) {
        if (empty($army['heroes'])) {
            new Cli_Logger('BRAK HEROSA');
            return self::firstBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
        } else {
            new Cli_Logger('JEST HEROS');
            new Cli_Logger($army['heroes'], 'HEROS:');
            $ruin = Cli_ComputerSubBlocks::getNearestRuin($castlesAndFields['fields'], Cli_Database::getFullRuins($gameId, $db), $army);
            if (!$ruin) {
                new Cli_Logger('BRAK RUIN');
                return self::firstBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
            } else {
                //idź do ruin
                new Cli_Logger('IDŹ DO RUIN');
                Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $ruin['currentPosition'], $db);
                Cli_SearchRuin::search($gameId, $ruin['ruinId'], $army['heroes'][0]['heroId'], $army['armyId'], $playerId, $db);
                return self::endMove($playerId, $db, $gameId, $army['armyId'], $ruin['currentPosition'], $ruin['path'], null, null, $ruin['ruinId']);
            }
        }
    }

    static public function moveArmy($gameId, $playerId, $army, $db = null) {
        new Cli_Logger('');
        new Cli_Logger($army['armyId'], 'armyId:');

        $canFlySwim = Cli_ComputerSubBlocks::getArmyCanFlySwim($army);
        $army['canFly'] = $canFlySwim['canFly'];
        $army['canSwim'] = $canFlySwim['canSwim'];
        $myCastles = Cli_Database::getPlayerCastles($gameId, $playerId, $db);
        $myCastleId = Application_Model_Board::isCastleAtPosition($army['x'], $army['y'], $myCastles);
        $fields = Cli_Database::getEnemyArmiesFieldsPositions($gameId, $playerId, $db);
        $razed = Cli_Database::getRazedCastles($gameId, $db);
        $castlesAndFields = Application_Model_Board::prepareCastlesAndFields($fields, $razed, $myCastles);
        $enemies = Cli_Database::getAllEnemiesArmies($gameId, $playerId, $db);

        if ($myCastleId !== null) {
            new Cli_Logger('W ZAMKU');

            $castlePosition = Application_Model_Board::getCastlePosition($myCastleId);
            $enemiesHaveRange = Cli_ComputerSubBlocks::canEnemyReachThisCastle($castlePosition, $castlesAndFields, $enemies);
            $enemiesInRange = Cli_ComputerSubBlocks::getEnemiesInRange($enemies, $army, $castlesAndFields['fields']);
            if (!$enemiesHaveRange) {
                new Cli_Logger('BRAK WROGA Z ZASIĘGIEM');

                if (!$enemiesInRange) {
                    new Cli_Logger('BRAK WROGA W ZASIĘGU');

                    return self::ruinBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
                } else {
                    new Cli_Logger('JEST WRÓG W ZASIĘGU');

                    foreach ($enemiesInRange as $e)
                    {
                        $castleId = Application_Model_Board::isCastleAtPosition($e['x'], $e['y'], $castlesAndFields['hostileCastles']);
                        if (Cli_ComputerSubBlocks::isEnemyStronger($gameId, $playerId, $db, $army, $e, $castleId)) {
                            continue;
                        } else {
                            $enemy = $e;
                            break;
                        }
                    }
                    if (isset($enemy)) {
                        new Cli_Logger('WRÓG JEST SŁABSZY - ATAKUJ!');

                        if ($castleId !== null) {
                            $range = Cli_ComputerSubBlocks::isEnemyCastleInRange($castlesAndFields, $castleId, $army);
                        } else {
                            $range = Cli_ComputerSubBlocks::isEnemyArmyInRange($castlesAndFields, $enemy, $army);
                        }
                        $fightEnemy = Cli_ComputerSubBlocks::fightEnemy($gameId, $army, $enemy, $playerId, $castleId, $db);
                        Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $range['currentPosition'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], $range['currentPosition'], $range['path'], $fightEnemy, $castleId);
                    } else {
                        new Cli_Logger('WRÓG JEST SILNIEJSZY - ZOSTAŃ!');

                        Cli_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    }
                }
            } else {
                new Cli_Logger('JEST WRÓG Z ZASIĘGIEM');

                if (!$enemiesInRange) {
                    new Cli_Logger('BRAK WROGA W ZASIĘGU - ZOSTAŃ!');

                    Cli_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                } else {
                    new Cli_Logger('JEST WRÓG W ZASIĘGU');

                    if (count($enemiesHaveRange) > count($enemiesInRange)) {
                        new Cli_Logger('WRÓGÓW Z ZASIĘGIEM > WRÓGÓW W ZASIĘGU - ZOSTAŃ!');

                        Cli_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    } else {
                        new Cli_Logger('WRÓGÓW Z ZASIĘGIEM <= WRÓGÓW W ZASIĘGU');

                        $enemy = Cli_ComputerSubBlocks::canAttackAllEnemyHaveRange($gameId, $playerId, $enemiesHaveRange, $army, $castlesAndFields['hostileCastles'], $db);
                        if (!$enemy) {
                            new Cli_Logger('NIE MOGĘ ZAATAKOWAĆ WRÓGÓW Z ZASIĘGIEM - ZOSTAŃ!');

                            Cli_Database::fortifyArmy($gameId, $playerId, $army['armyId'], $db);
                            return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                        } else {
                            //atakuj
                            new Cli_Logger('ATAKUJĘ WRÓGÓW Z ZASIĘGIEM - ATAKUJ!'); //atakuję wrogów którzy mają zasięg na zamek, brak enemy armyId, armia nie zmienia pozycji

                            $aStar = $enemy['aStar'];
                            $aStar->getPath($enemy['key'], $enemy['movesToSpend']);
                            $path = $aStar->reversePath();
                            $currentPosition = $aStar->getCurrentPosition();
                            $fightEnemy = Cli_ComputerSubBlocks::fightEnemy($gameId, $army, $enemy, $playerId, $enemy['castleId'], $db);
                            Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $currentPosition, $db);
                            return self::endMove($playerId, $db, $gameId, $army['armyId'], $currentPosition, $path, $fightEnemy, $enemy['castleId']);
                        }
                    }
                }
            }
        } else {
            new Cli_Logger('POZA ZAMKIEM');

            $myEmptyCastle = Cli_ComputerSubBlocks::getMyEmptyCastleInMyRange($gameId, $myCastles, $army, $castlesAndFields['fields'], $db);
            if (!$myEmptyCastle) {
                new Cli_Logger('NIE MA MOJEGO PUSTEGO ZAMKU W ZASIĘGU');

                return self::ruinBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
            } else {
                new Cli_Logger('JEST MÓJ PUSTY ZAMEK W ZASIĘGU');

                if (!Cli_ComputerSubBlocks::isMyCastleInRangeOfEnemy($enemies, $myEmptyCastle, $castlesAndFields['fields'])) {
                    new Cli_Logger('WRÓG NIE MA ZASIĘGU NA PUSTY ZAMEK');

                    return self::firstBlock($gameId, $playerId, $enemies, $army, $castlesAndFields, $myCastles, $db);
                } else {
                    //idź do zamku
                    new Cli_Logger('WRÓG MA ZASIĘG NA PUSTY ZAMEK - IDŹ DO ZAMKU!');

                    $data = array(
                        'x' => $myEmptyCastle['x'],
                        'y' => $myEmptyCastle['y'],
                        'movesSpend' => $army['movesLeft']
                    );
                    Cli_Database::updateArmyPosition($gameId, $army['armyId'], $playerId, $data, $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $myEmptyCastle['currentPosition'], $myEmptyCastle['path']);
                }
            }
        }
    }

    static private function endMove($playerId, $db, $gameId, $oldArmyId, $position, $path = null, $fightEnemy = null, $castleId = null, $ruinId = null) {

        $armiesIds = Cli_Database::joinArmiesAtPosition($gameId, $position, $playerId, $db);
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
            $attackerArmy = Cli_Database::getArmyByArmyIdPlayerId($gameId, $armyId, $playerId, $db);
            $defenderArmy = null;
        }

//        print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4));

        return array(
            'defenderColor' => $fightEnemy['defenderColor'],
            'defenderArmy' => $defenderArmy,
            'attackerColor' => Cli_Database::getColorByPlayerId($gameId, $playerId, $db),
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

    static public function startTurn($gameId, $playerId, $db = null) {
        Cli_Database::turnActivate($gameId, $playerId, $db);
        Cli_Database::unfortifyComputerArmies($gameId, $playerId, $db);
        Cli_Database::resetHeroesMovesLeft($gameId, $playerId, $db);
        Cli_Database::resetSoldiersMovesLeft($gameId, $playerId, $db);

        $gold = Cli_Database::getPlayerInGameGold($gameId, $playerId, $db);
        $income = 0;
        $castles = array();
        $color = null;
        $turnNumber = Cli_Database::getTurnNumber($gameId, $db);

        if ($turnNumber == 0) {
            var_dump('?');
            return;
        }

        $castlesId = Cli_Database::getPlayerCastlesIds($gameId, $playerId, $db);
        foreach ($castlesId as $id)
        {
            $castleId = $id['castleId'];
            $castles[$castleId] = Application_Model_Board::getCastle($castleId);
            $castle = $castles[$castleId];
            $income += $castle['income'];
            $castleProduction = Cli_Database::getCastleProduction($gameId, $castleId, $playerId, $db);
            if ($turnNumber < 10) {
                $unitName = Application_Model_Board::getMinProductionTimeUnit($castleId);
            } else {
                $unitName = Application_Model_Board::getCastleOptimalProduction($castleId);
            }
            $unitId = Cli_Database::getUnitIdByName($unitName, $db);
            if ($unitId != $castleProduction['production']) {
                Cli_Database::setCastleProduction($gameId, $castleId, $unitId, $playerId, $db);
                $castleProduction = Cli_Database::getCastleProduction($gameId, $castleId, $playerId, $db);
            }
            $castles[$castleId]['productionTurn'] = $castleProduction['productionTurn'];
            $unitName = Application_Model_Board::getUnitName($castleProduction['production']);
            if ($castle['production'][$unitName]['time'] <= $castleProduction['productionTurn'] AND $castle['production'][$unitName]['cost'] <= $gold) {
                if (Cli_Database::resetProductionTurn($gameId, $castleId, $playerId, $db) == 1) {
                    $armyId = Cli_Database::getArmyIdFromPosition($gameId, $castle['position'], $db);
                    if (!$armyId) {
                        $armyId = Cli_Database::createArmy($gameId, $db, $castle['position'], $playerId);
                    }
                    Cli_Database::addSoldierToArmy($gameId, $armyId, $castleProduction['production'], $db);
                }
            }
        }
        if (isset($castle['position'])) {
            $gold = self::handleHeroResurrection($gameId, $gold, $castle['position'], $playerId, $db);
        }

        $armies = Cli_Database::getPlayerArmies($gameId, $playerId, $db);

        if (empty($castles) && empty($armies)) {
            $action = 'gameover';
        } else {
            $income += Cli_Database::calculateIncomeFromTowers($gameId, $playerId, $db);
            $gold = $gold + $income - Cli_Database::calculateCostsOfSoldiers($gameId, $playerId, $db);
            Cli_Database::updatePlayerInGameGold($gameId, $playerId, $gold, $db);
            $action = 'start';
            $color = Cli_Database::getColorByPlayerId($gameId, $playerId, $db);
        }

        return array(
            'action' => $action,
            'armies' => $armies,
            'color' => $color
        );
    }

    static private function handleHeroResurrection($gameId, $gold, $position, $playerId, $db = null) {
        if (!Cli_Database::isHeroInGame($gameId, $playerId, $db)) {
            Cli_Database::connectHero($gameId, $playerId, $db);
        }
        $heroId = Cli_Database::getDeadHeroId($gameId, $playerId, $db);
        if ($heroId) {
            if ($gold >= 100) {
                $armyId = Cli_Database::heroResurection($gameId, $heroId, $position, $playerId, $db);
                if ($armyId) {
                    return $gold - 100;
                }
            }
        }
        return $gold;
    }

}

