<?php

class Cli_Model_Turn
{

    static public function next($gameId, $playerId, $db)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);

        if ($mPlayersInGame->playerLost($playerId)) {
            return;
        }

        $response = array();

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $nextPlayer = array(
            'color' => $playersInGameColors[$playerId]
        );

        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $mGame = new Application_Model_Game($gameId, $db);
        $mArmy = new Application_Model_Army($gameId, $db);

        while (empty($response)) {
            $nextPlayer = self::getExpectedNextTurnPlayer($gameId, $nextPlayer['color'], $db);
            $playerCastlesExists = $mCastlesInGame->playerCastlesExists($nextPlayer['playerId']);
            $playerArmiesExists = $mArmy->playerArmiesExists($nextPlayer['playerId']);
            if ($playerCastlesExists || $playerArmiesExists) {
                $response['color'] = $nextPlayer['color'];

                if ($nextPlayer['playerId'] == $playerId) { // następny gracz to ten sam gracz, który zainicjował zmianę tury
                    $response['win'] = true;
                    $mGame->endGame(); // koniec gry
                } else { // zmieniam turę
                    $mGame->updateTurnNumber($nextPlayer);
                    $mCastlesInGame->increaseAllCastlesProductionTurn($playerId);

                    $turn = $mGame->getTurn();

                    $response['lost'] = $turn['lost'];
                    $response['nr'] = $turn['nr'];
                }
            } else {
                $mPlayersInGame->setPlayerLostGame($nextPlayer['playerId']);
            }
        }

        $mTurn = new Application_Model_Turn($gameId, $db);
        $mTurn->insertTurn($playerId, $response['nr']);

        return $response;
    }

    static public function start($gameId, $playerId, $db, $computer = null)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
        $mPlayersInGame->turnActivate($playerId);

        $mArmy = new Application_Model_Army($gameId, $db);
        $mSoldier = new Application_Model_Soldier($gameId, $db);
        $mSoldier->resetMovesLeft($mArmy->getSelectForPlayerAll($playerId));

        $gold = $mPlayersInGame->getPlayerInGameGold($playerId);
        if ($computer) {
            $mArmy->unfortifyComputerArmies($playerId);
        }
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->resetHeroesMovesLeft($playerId);

        $income = 0;
        $color = null;

        $mapCastles = Zend_Registry::get('castles');

        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $castlesInGame = $mCastlesInGame->getPlayerCastles($playerId);

        foreach ($castlesInGame as $castleId => $castleInGame) {
            $income += $mapCastles[$castleId]['income'];

            $castleProduction = $mCastlesInGame->getProduction($castleId, $playerId);

            if ($computer) {
                if (isset($mapCastles[$castleId]['position'])) {
                    $gold = Cli_Model_ComputerMainBlocks::handleHeroResurrection($gameId, $gold, $mapCastles[$castleId]['position'], $playerId, $db);
                }

                $mGame = new Application_Model_Game($gameId, $db);
                $turnNumber = $mGame->getTurnNumber();

                if ($turnNumber < 10) {
                    $unitId = Application_Model_Board::getMinProductionTimeUnit($mapCastles[$castleId]['production']);
                } else {
                    $unitId = Application_Model_Board::getCastleOptimalProduction($mapCastles[$castleId]['production']);
                }
                if ($unitId != $castleProduction['productionId']) {
                    $mCastlesInGame->setProduction($castleId, $playerId, $unitId);
                    $castleProduction = $mCastlesInGame->getProduction($castleId, $playerId);
                }
            } else {
                $unitId = $castleProduction['productionId'];
            }

            $castlesInGame[$castleId]['productionTurn'] = $castleProduction['productionTurn'];

            if ($unitId && $mapCastles[$castleId]['production'][$unitId]['time'] <= $castleProduction['productionTurn'] AND $mapCastles[$castleId]['production'][$unitId]['cost'] <= $gold) {
                if ($mCastlesInGame->resetProductionTurn($castleId, $playerId) == 1) {
                    $armyId = $mArmy->getArmyIdFromPosition($mapCastles[$castleId]['position']);
                    if (!$armyId) {
                        $armyId = $mArmy->createArmy($mapCastles[$castleId]['position'], $playerId);
                    }
                    $mSoldier = new Application_Model_Soldier($gameId, $db);
                    $mSoldier->add($armyId, $unitId);
                }
            }
        }

        $armies = $mArmy->getPlayerArmiesWithUnits($playerId);

        if (empty($castlesInGame) && empty($armies)) {
            return array('action' => 'gameover');
        } else {
            $costs = $mSoldier->calculateCostsOfSoldiers($mArmy->getSelectForPlayerAll($playerId));
            $mTowersInGame = new Application_Model_TowersInGame($gameId, $db);
            $income += $mTowersInGame->calculateIncomeFromTowers($playerId);
            $gold = $gold + $income - $costs;

            $mPlayersInGame->updatePlayerInGameGold($playerId, $gold);

            $playersInGameColors = Zend_Registry::get('playersInGameColors');

            return array(
                'action' => 'start',
                'gold' => $gold,
                'costs' => $costs,
                'income' => $income,
                'armies' => $armies,
                'castles' => $castlesInGame,
                'color' => $playersInGameColors[$playerId]
            );
        }
    }

    static public function getExpectedNextTurnPlayer($gameId, $playerColor, $db)
    {

        $find = false;

        $playerColors = Zend_Registry::get('colors');

        /* szukam następnego koloru w dostępnych kolorach */
        foreach ($playerColors as $color) {
            /* znajduję kolor gracza, który ma aktualnie turę i przewijam na następny */
            if ($playerColor == $color) {
                $find = true;
                continue;
            }

            /* to jest przewinięty kolor gracza */
            if ($find) {
                $nextPlayerColor = $color;
                break;
            }
        }

        if (!isset($nextPlayerColor)) {
            $nextPlayerColor = $playerColors[0];
        }

        $mPlayersInGame = new Application_Model_PlayersInGame($gameId, $db);
        $playersInGame = $mPlayersInGame->getPlayersInGameReady();

        /* przypisuję playerId do koloru */
        foreach ($playersInGame as $k => $player) {
            if ($player['color'] == $nextPlayerColor) {
                $nextPlayerId = $player['playerId'];
                break;
            }
        }

        /* jeśli nie znalazłem następnego gracza to następnym graczem jest gracz pierwszy */
        if (!isset($nextPlayerId)) {
            foreach ($playersInGame as $k => $player) {
                if ($player['color'] == $playerColors[0]) {
                    if ($player['lost']) {
                        $nextPlayerId = $playersInGame[$k + 1]['playerId'];
                        $nextPlayerColor = $playersInGame[$k + 1]['color'];
                    } else {
                        $nextPlayerId = $player['playerId'];
                        $nextPlayerColor = $player['color'];
                    }
                    break;
                }
            }
        }

        if (!isset($nextPlayerId)) {
            echo('Błąd! Nie znalazłem gracza');

            return;
        }

        return array(
            'playerId' => $nextPlayerId,
            'color' => $nextPlayerColor
        );
    }

}
