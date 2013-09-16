<?php

class Cli_Model_Turn
{

    static public function next($gameId, $playerId, $db)
    {
        if (Cli_Model_Database::playerLost($gameId, $playerId, $db)) {
            return;
        }

        $response = array();
        $nextPlayer = array(
            'color' => Cli_Model_Database::getColorByPlayerId($gameId, $playerId, $db)
        );

        while (empty($response)) {
            $nextPlayer = self::getExpectedNextTurnPlayer($gameId, $nextPlayer['color'], $db);
            $playerCastlesExists = Cli_Model_Database::playerCastlesExists($gameId, $nextPlayer['playerId'], $db);
            $playerArmiesExists = Cli_Model_Database::playerArmiesExists($gameId, $nextPlayer['playerId'], $db);
            if ($playerCastlesExists || $playerArmiesExists) {
                $response['color'] = $nextPlayer['color'];

                if ($nextPlayer['playerId'] == $playerId) { // następny gracz to ten sam gracz, który zainicjował zmianę tury
                    $response['win'] = true;
                    Cli_Model_Database::endGame($gameId, $db); // koniec gry
                } else { // zmieniam turę
                    Cli_Model_Database::updateTurnNumber($gameId, $nextPlayer, $db);
                    Cli_Model_Database::raiseAllCastlesProductionTurn($gameId, $playerId, $db);
                    $turn = Cli_Model_Database::getTurn($gameId, $db);
                    $response['lost'] = $turn['lost'];
                    $response['nr'] = $turn['nr'];
                }
            } else {
                Cli_Model_Database::setPlayerLostGame($gameId, $nextPlayer['playerId'], $db);
            }
        }

        return $response;
    }

    static public function start($gameId, $playerId, $db = null)
    {
        if (!$db) {
            $db = self::getDb();
        }

        $income = 0;

        Cli_Model_Database::turnActivate($gameId, $playerId, $db);
        Cli_Model_Database::resetHeroesMovesLeft($gameId, $playerId, $db);

        $mArmy = new Application_Model_Army($gameId, $db);
        $mSoldier = new Application_Model_Soldier($gameId, $db);
        $mSoldier->resetMovesLeft($mArmy->getSelectForPlayerAll($playerId));

        $gold = Cli_Model_Database::getPlayerInGameGold($gameId, $playerId, $db);

//        if (Cli_Database::getTurnNumber($gameId, $db) > 0) {
        $castles = Cli_Model_Database::getPlayerCastles($gameId, $playerId, $db);
        $mapCastles = Zend_Registry::get('castles');
        foreach ($castles as $castleInGame) {
            $castleId = $castleInGame['castleId'];
//                $castles[$castleId] = Application_Model_Board::getCastle($castleId);
//                $castle = $castles[$castleId];
//            $boardCastle = Application_Model_Board::getCastle($castleId);
            $boardCastle = $mapCastles[$castleId];
            $income += $boardCastle['income'];
            $armyId = Cli_Model_Database::getArmyIdFromPosition($gameId, $boardCastle['position'], $db);

//                $castleProduction = Cli_Database::getCastleProduction($gameId, $castleId, $playerId, $db);
//                    $castles[$castleId]['productionTurn'] = $castleProduction['productionTurn'];

            $unitId = $castleInGame['production'];
            if ($castleInGame['production'] AND
                $boardCastle['production'][$unitId]['time'] <= $castleInGame['productionTurn']
                AND $boardCastle['production'][$unitId]['cost'] <= $gold
            ) {
                if (!$armyId) {
                    $mArmy = new Application_Model_Army($gameId, $db);
                    $armyId = $mArmy->createArmy($boardCastle['position'], $playerId);
                }

                Cli_Model_Database::resetProductionTurn($gameId, $castleId, $playerId, $db);
                if (!isset($mSoldier)) {
                    $mSoldier = new Application_Model_Soldier($gameId, $db);
                }
                $mSoldier->add($armyId, $castleInGame['production']);
            }
        }
//        }
        $armies = Cli_Model_Database::getPlayerArmies($gameId, $playerId, $db);
        if (empty($castles) && empty($armies)) {
            return array('gameover' => 1);
        } else {
            if (!isset($mSoldier)) {
                $mSoldier = new Application_Model_Soldier($gameId, $db);
            }
            if (!isset($mArmy)) {
                $mArmy = new Application_Model_Army($gameId, $db);
            }
            $costs = $mSoldier->calculateCostsOfSoldiers($mArmy->getSelectForPlayerAll($playerId));
            $mTowersInGame = new Application_Model_TowersInGame($gameId, $db);
            $income += $mTowersInGame->calculateIncomeFromTowers($playerId);
            $gold = $gold + $income - $costs;
            Cli_Model_Database::updatePlayerInGameGold($gameId, $playerId, $gold, $db);

            return array(
                'gold' => $gold,
                'costs' => $costs,
                'income' => $income,
                'armies' => $armies,
                'color' => Cli_Model_Database::getColorByPlayerId($gameId, $playerId, $db)
            );
        }
    }

    static public function getExpectedNextTurnPlayer($gameId, $playerColor, $db)
    {

        $find = false;

        $playerColors = Zend_Registry::get('colors');

        /* szukam następnego koloru w dostępnych kolorach */
        foreach ($playerColors as $color) {

            echo $color;
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

        $playersInGame = Cli_Model_Database::getPlayersInGameReady($gameId, $db);

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
