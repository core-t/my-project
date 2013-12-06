<?php

class Cli_Model_ComputerMainBlocks
{

    static private function firstBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db)
    {
        $l = new Coret_Model_Logger();
        $army = $mArmy->getArmy();
        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        if (!$mCastlesInGame->enemiesCastlesExist($playerId)) {
            $l->log('BRAK ZAMKÓW WROGA');
            return self::secondBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db);
        } else {
            $l->log('SĄ ZAMKI WROGA');
            $castleId = Cli_Model_ComputerSubBlocks::getWeakerEnemyCastle($gameId, $castlesAndFields['hostileCastles'], $army, $playerId, $db);
            if ($castleId !== null) {
                $l->log('JEST SŁABSZY ZAMEK WROGA');
                $castleRange = Cli_Model_ComputerSubBlocks::isEnemyCastleInRange($castlesAndFields, $castleId, $mArmy);
                if ($castleRange['in']) {
                    //atakuj
                    $l->log('SŁABSZY ZAMEK WROGA W ZASIĘGU - ATAKUJĘ!');
                    $fightEnemy = Cli_Model_ComputerSubBlocks::fightEnemy($gameId, $army, $castleRange['path'], $castlesAndFields['fields'], null, $playerId, $castleId, $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $castleRange['currentPosition'], $castleRange['path'], $fightEnemy, $castleId);
                } else {
                    $l->log('SŁABSZY ZAMEK WROGA POZA ZASIĘGIEM');
                    $enemy = Cli_Model_ComputerSubBlocks::getWeakerEnemyArmyInRange($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $db);
                    if ($enemy) {
                        //atakuj
                        $l->log('JEST SŁABSZA ARMIA WROGA W ZASIĘGU - ATAKUJĘ!');
                        $fightEnemy = Cli_Model_ComputerSubBlocks::fightEnemy($gameId, $army, $enemy['path'], $castlesAndFields['fields'], $enemy, $playerId, $enemy['castleId'], $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], $enemy['currentPosition'], $enemy['path'], $fightEnemy, $enemy['castleId']);
                    } else {
                        $l->log('BRAK SŁABSZEJ ARMII WROGA W ZASIĘGU');
                        $enemy = Cli_Model_ComputerSubBlocks::getStrongerEnemyArmyInRange($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $db);
                        $mArmy2 = new Application_Model_Army($gameId, $db);
                        if ($enemy) {
                            $l->log('JEST SILNIEJSZA ARMIA WROGA W ZASIĘGU');
                            $join = Cli_Model_ComputerSubBlocks::getMyArmyInRange($gameId, $playerId, $mArmy, $castlesAndFields['fields'], $db);
                            if ($join) {
                                $l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                                $mArmy2->updateArmyPosition($playerId, $join['path'], $castlesAndFields['fields'], $army);
                                return self::endMove($playerId, $db, $gameId, $army['armyId'], $join['currentPosition'], $join['path']);
                            } else {
                                $l->log('BRAK MOJEJ ARMII W ZASIĘGU - IDŹ W KIERUNKU ZAMKU!');
                                $mArmy2->updateArmyPosition($playerId, $castleRange['path'], $castlesAndFields['fields'], $army);
                                $mArmy2->fortify($army['armyId'], 1);
                                return self::endMove($playerId, $db, $gameId, $army['armyId'], $castleRange['currentPosition'], $castleRange['path']);
                            }
                        } else {
                            $l->log('BRAK SILNIEJSZEJ ARMII WROGA W ZASIĘGU - IDŹ W KIERUNKU ZAMKU!');
                            $mArmy2->updateArmyPosition($playerId, $castleRange['path'], $castlesAndFields['fields'], $army);
                            $mArmy2->fortify($army['armyId'], 1);
                            return self::endMove($playerId, $db, $gameId, $army['armyId'], $castleRange['currentPosition'], $castleRange['path']);
                        }
                    }
                }
            } else {
                $l->log('BRAK SŁABSZYCH ZAMKÓW WROGA');
                return self::secondBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db);
            }
        }
    }

    static private function secondBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db)
    {
        $l = new Coret_Model_Logger();
        $army = $mArmy->getArmy();
        if (!$enemies) {
            return array(
                'action' => 'end'
            );
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
                $l->log('WRÓG JEST SŁABSZY');
                $range = Cli_Model_ComputerSubBlocks::isEnemyArmyInRange($castlesAndFields, $enemy, $mArmy);
                if ($range['in']) {
                    $l->log('SŁABSZY WRÓG W ZASIĘGU - ATAKUJ!');
                    $fightEnemy = Cli_Model_ComputerSubBlocks::fightEnemy($gameId, $army, $range['path'], $castlesAndFields['fields'], $enemy, $playerId, $range['castleId'], $db);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $range['currentPosition'], $range['path'], $fightEnemy);
                } else {
                    $l->log('SŁABSZY WRÓG POZA ZASIĘGIEM - IDŹ DO WROGA');
                    $mArmy2 = new Application_Model_Army($gameId, $db);
                    $mArmy2->updateArmyPosition($playerId, $range['path'], $castlesAndFields['fields'], $army);
                    $mArmy2->fortify($army['armyId'], 1);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $range['currentPosition'], $range['path']);
                }
            } else {
                $l->log('WRÓG JEST SILNIEJSZY');
                $join = Cli_Model_ComputerSubBlocks::getMyArmyInRange($gameId, $playerId, $mArmy, $castlesAndFields['fields'], $db);
                $mArmy2 = new Application_Model_Army($gameId, $db);
                if ($join) {
                    $l->log('JEST MOJA ARMIA W ZASIĘGU - DOŁĄCZ!');
                    $mArmy2->updateArmyPosition($playerId, $join['path'], $castlesAndFields['fields'], $army);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $join['currentPosition'], $join['path']);
                } else {
                    $l->log('BRAK MOJEJ ARMII W ZASIĘGU');
                    $castle = Cli_Model_ComputerSubBlocks::getMyCastleNearEnemy($enemies, $mArmy, $castlesAndFields['fields'], $myCastles);
                    if ($castle) {
                        $l->log('JEST MÓJ ZAMEK W POBLIŻU WROGA - IDŹ DO ZAMKU');
                        $mArmy2->updateArmyPosition($playerId, $castle['path'], $castlesAndFields['fields'], $army);
                        $mArmy2->fortify($army['armyId'], 1);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], $castle['currentPosition'], $castle['path']);
                    } else {
                        $l->log('NIE MA MOJEGO ZAMKU W POBLIŻU WROGA - ZOSTAŃ');
                        $mArmy2->fortify($army['armyId'], 1);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    }
                }
            }
        }
    }

    static private function ruinBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db)
    {
        $l = new Coret_Model_Logger();
        $army = $mArmy->getArmy();
        if (empty($army['heroes'])) {
            $l->log('BRAK HEROSA');
            return self::firstBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db);
        } else {
            $l->log('JEST HEROS');

            $mRuinsInGame = new Application_Model_RuinsInGame($gameId, $db);
            $ruin = Cli_Model_ComputerSubBlocks::getNearestRuin($castlesAndFields['fields'], $mRuinsInGame->getFullRuins(), $mArmy);

            if (!$ruin) {
                $l->log('BRAK RUIN');
                return self::firstBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db);
            } else {
                //idź do ruin
                $l->log('IDŹ DO RUIN');
                $mArmy2 = new Application_Model_Army($gameId, $db);
                $mArmy2->updateArmyPosition($playerId, $ruin['path'], $castlesAndFields['fields'], $army);
                Cli_Model_SearchRuin::search($gameId, $ruin['ruinId'], $army['heroes'][0]['heroId'], $army['armyId'], $playerId, $db);

                $mArmy2 = new Application_Model_Army($gameId, $db);
                $mArmy2->fortify($army['armyId'], 1);
                return self::endMove($playerId, $db, $gameId, $army['armyId'], $ruin['currentPosition'], $ruin['path'], null, null, $ruin['ruinId']);
            }
        }
    }

    static public function moveArmy($gameId, $playerId, $mArmy, $db)
    {
        $l = new Coret_Model_Logger();
        $army = $mArmy->getArmy();
        $l->log('');
        $l->log($army['armyId'], 'armyId:');

        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $myCastles = $mCastlesInGame->getPlayerCastles($playerId);

        $mapCastles = Zend_Registry::get('castles');
        foreach ($myCastles as $myCastleId => $myCastle) {
            $myCastles[$myCastleId]['position'] = $mapCastles[$myCastleId]['position'];
        }
        $myCastleId = Application_Model_Board::isCastleAtPosition($army['x'], $army['y'], $myCastles);

        $mArmy2 = new Application_Model_Army($gameId, $db);

        $fields = $mArmy2->getEnemyArmiesFieldsPositions($playerId);
        $razed = $mCastlesInGame->getRazedCastles();
        $castlesAndFields = Application_Model_Board::prepareCastlesAndFields($fields, $razed, $myCastles);

        $enemies = $mArmy2->getAllEnemiesArmies($playerId);

        if ($myCastleId !== null) {
            $l->log('W ZAMKU');

            $castlePosition = $myCastles[$myCastleId]['position'];
            $enemiesHaveRange = Cli_Model_ComputerSubBlocks::getEnemiesHaveRangeAtThisCastle($castlePosition, $castlesAndFields, $enemies);
            $enemiesInRange = Cli_Model_ComputerSubBlocks::getEnemiesInRange($enemies, $mArmy, $castlesAndFields['fields']);
            if (!$enemiesHaveRange) {
                $l->log('BRAK WROGA Z ZASIĘGIEM');

                if (!$enemiesInRange) {
                    $l->log('BRAK WROGA W ZASIĘGU');

                    return self::ruinBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db);
                } else {
                    $l->log('JEST WRÓG W ZASIĘGU');

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
                        $l->log('WRÓG JEST SŁABSZY - ATAKUJ!');

                        if ($castleId !== null) {
                            $range = Cli_Model_ComputerSubBlocks::isEnemyCastleInRange($castlesAndFields, $castleId, $mArmy);
                        } else {
                            $range = Cli_Model_ComputerSubBlocks::isEnemyArmyInRange($castlesAndFields, $enemy, $mArmy);
                        }
                        $fightEnemy = Cli_Model_ComputerSubBlocks::fightEnemy($gameId, $army, $range['path'], $castlesAndFields['fields'], $enemy, $playerId, $castleId, $db);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], $range['currentPosition'], $range['path'], $fightEnemy, $castleId);
                    } else {
                        $l->log('WRÓG JEST SILNIEJSZY - ZOSTAŃ!');

                        $mArmy2->fortify($army['armyId'], 1);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    }
                }
            } else {
                $l->log('JEST WRÓG Z ZASIĘGIEM');

                if (!$enemiesInRange) {
                    $l->log('BRAK WROGA W ZASIĘGU - ZOSTAŃ!');

                    $mArmy2->fortify($army['armyId'], 1);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                } else {
                    $l->log('JEST WRÓG W ZASIĘGU');

                    if (count($enemiesHaveRange) > 1) {
                        $l->log('WRÓGÓW Z ZASIĘGIEM > WRÓGÓW W ZASIĘGU - ZOSTAŃ!');

                        $mArmy2->fortify($army['armyId'], 1);
                        return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                    } else {
                        $l->log('TYLKO JEDEN WRÓGÓW Z ZASIĘGIEM');

                        $enemy = Cli_Model_ComputerSubBlocks::canAttackAllEnemyHaveRange($gameId, $playerId, $enemiesHaveRange, $army, $castlesAndFields['hostileCastles'], $db);
                        if (!$enemy) {
                            $l->log('NIE MOGĘ ZAATAKOWAĆ WRÓGÓW Z ZASIĘGIEM - ZOSTAŃ!');

                            $mArmy2->fortify($army['armyId'], 1);
                            return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                        } else {
                            $range = Cli_Model_ComputerSubBlocks::isEnemyArmyInRange($castlesAndFields, $enemy, $mArmy);
                            if ($range['in']) {
                                $l->log('ATAKUJĘ WRÓGA Z ZASIĘGIEM - ATAKUJ!');

                                $fightEnemy = Cli_Model_ComputerSubBlocks::fightEnemy($gameId, $army, $range['path'], $castlesAndFields['fields'], $enemy, $playerId, $range['castleId'], $db);
                                return self::endMove($playerId, $db, $gameId, $army['armyId'], $range['currentPosition'], $range['path'], $fightEnemy);
                            } else {
                                $l->log('WRÓG Z ZASIĘGIEM POZA ZASIĘGIEM - ZOSTAŃ!');

                                $mArmy2->fortify($army['armyId'], 1);
                                return self::endMove($playerId, $db, $gameId, $army['armyId'], array('x' => $army['x'], 'y' => $army['y']));
                            }
//                            //atakuj
//                            $l->log('ATAKUJĘ WRÓGA Z ZASIĘGIEM - ATAKUJ!'); //atakuję wrogów którzy mają zasięg na zamek, brak enemy armyId, armia nie zmienia pozycji
//                            $aStar = $enemy['aStar'];
//                            $path = $aStar->getReturnPath($enemy['key']);
////                            $path = $aStar->getPath($enemy['key']);
//                            echo '*** TEST RETURN PATH ***' . "\n";
//                            echo '*** TEST RETURN PATH ***' . "\n";
//                            echo '*** TEST RETURN PATH ***' . "\n";
//                            var_dump($path);
//                            echo '*** TEST RETURN PATH ***' . "\n";
//                            echo '*** TEST RETURN PATH ***' . "\n";
//                            echo '*** TEST RETURN PATH ***' . "\n";
////                            exit;
//                            $fightEnemy = Cli_Model_ComputerSubBlocks::fightEnemy($gameId, $army, $path, $castlesAndFields['fields'], $enemy, $playerId, $enemy['castleId'], $db);
//                            return self::endMove($playerId, $db, $gameId, $army['armyId'], $enemy['currentPosition'], $path, $fightEnemy, $enemy['castleId']);
                        }
                    }
                }
            }
        } else {
            $l->log('POZA ZAMKIEM');

            $myEmptyCastle = Cli_Model_ComputerSubBlocks::getMyEmptyCastleInMyRange($gameId, $myCastles, $mArmy, $castlesAndFields['fields'], $db);
            if (!$myEmptyCastle) {
                $l->log('NIE MA MOJEGO PUSTEGO ZAMKU W ZASIĘGU');

                return self::ruinBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db);
            } else {
                $l->log('JEST MÓJ PUSTY ZAMEK W ZASIĘGU');

                if (!Cli_Model_ComputerSubBlocks::isMyCastleInRangeOfEnemy($enemies, $myEmptyCastle, $castlesAndFields['fields'])) {
                    $l->log('WRÓG NIE MA ZASIĘGU NA PUSTY ZAMEK');

                    return self::firstBlock($gameId, $playerId, $enemies, $mArmy, $castlesAndFields, $myCastles, $db);
                } else {
                    //idź do zamku
                    $l->log('WRÓG MA ZASIĘG NA PUSTY ZAMEK - IDŹ DO ZAMKU!');

                    $mArmy2->updateArmyPosition($playerId, $myEmptyCastle['path'], $castlesAndFields['fields'], $army);
                    return self::endMove($playerId, $db, $gameId, $army['armyId'], $myEmptyCastle['currentPosition'], $myEmptyCastle['path']);
                }
            }
        }
    }

    static private function endMove($playerId, $db, $gameId, $oldArmyId, $position, $path = null, $fightEnemy = null, $castleId = null, $ruinId = null)
    {
        $mArmy2 = new Application_Model_Army($gameId, $db);
        $armiesIds = $mArmy2->joinArmiesAtPosition($position, $playerId);
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
            $attackerArmy = $mArmy2->getArmyByArmyIdPlayerId($armyId, $playerId);
            $defenderArmy = null;
        }

//        print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4));

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        return array(
            'defenderColor' => $fightEnemy['defenderColor'],
            'defenderArmy' => $defenderArmy,
            'attackerColor' => $playersInGameColors[$playerId],
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

    static public function handleHeroResurrection($gameId, $playerId, $db, $gameHandler)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
        $gold = $mPlayersInGame->getPlayerGold($playerId);

        if ($gold < 100) {
            return;
        }

        $capitals = Zend_Registry::get('capitals');
        $playersInGameColors = Zend_Registry::get('playersInGameColors');
        $color = $playersInGameColors[$playerId];
        $castleId = $capitals[$color];

        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        if (!$mCastlesInGame->isPlayerCastle($castleId, $playerId)) {
            return;
        }

        $mapCastles = Zend_Registry::get('castles');

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $heroId = $mHeroesInGame->getDeadHeroId($playerId);

        if (!$heroId) {
            return;
        }

        $armyId = Cli_Model_Army::heroResurrection($gameId, $heroId, $mapCastles[$castleId]['position'], $playerId, $db);

        if (!$armyId) {
            return;
        }

        $gold -= 100;
        $mPlayersInGame->updatePlayerGold($playerId, $gold);

        $mArmy2 = new Application_Model_Army($gameId, $db);

        $token = array(
            'type' => 'resurrection',
            'data' => array(
                'army' => $mArmy2->getArmyByArmyId($armyId),
                'gold' => $gold
            ),
            'color' => $color
        );

        $gameHandler->sendToChannel($db, $token, $gameId);
    }

}

