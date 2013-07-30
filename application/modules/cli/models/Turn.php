<?php

class Cli_Model_Turn {

    static public function next($gameId, $playerId, $db) {
        if (Cli_Model_Database::playerLost($gameId, $playerId, $db)) {
            return;
        }

        $response = array();
        $nextPlayer = array(
            'color' => Cli_Model_Database::getColorByPlayerId($gameId, $playerId, $db)
        );

        while (empty($response))
        {
            $nextPlayer = Cli_Model_Database::getExpectedNextTurnPlayer($gameId, $nextPlayer['color'], $db);
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

    static public function start($gameId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }

        $income = 0;

        Cli_Model_Database::turnActivate($gameId, $playerId, $db);
        Cli_Model_Database::resetHeroesMovesLeft($gameId, $playerId, $db);
        Cli_Model_Database::resetSoldiersMovesLeft($gameId, $playerId, $db);

        $gold = Cli_Model_Database::getPlayerInGameGold($gameId, $playerId, $db);

//        if (Cli_Database::getTurnNumber($gameId, $db) > 0) {
        $castles = Cli_Model_Database::getPlayerCastles($gameId, $playerId, $db);
        $mapCastles = Zend_Registry::get('castles');
        foreach ($castles as $dbCastle)
        {
            $castleId = $dbCastle['castleId'];
//                $castles[$castleId] = Application_Model_Board::getCastle($castleId);
//                $castle = $castles[$castleId];
//            $boardCastle = Application_Model_Board::getCastle($castleId);
            $boardCastle = $mapCastles[$castleId];
            $income += $boardCastle['income'];
            $armyId = Cli_Model_Database::getArmyIdFromPosition($gameId, $boardCastle['position'], $db);

//                $castleProduction = Cli_Database::getCastleProduction($gameId, $castleId, $playerId, $db);
//                    $castles[$castleId]['productionTurn'] = $castleProduction['productionTurn'];

            $unitId = $dbCastle['production'];
            if ($dbCastle['production'] AND
                    $boardCastle['production'][$unitId]['time'] <= $dbCastle['productionTurn']
                    AND $boardCastle['production'][$unitId]['cost'] <= $gold
            ) {
                if (!$armyId) {
                    $armyId = Cli_Model_Database::createArmy($gameId, $db, $boardCastle['position'], $playerId);
                }

                Cli_Model_Database::resetProductionTurn($gameId, $castleId, $playerId, $db);
                Cli_Model_Database::addSoldierToArmy($gameId, $armyId, $dbCastle['production'], $db);
            }
        }
//        }
        $armies = Cli_Model_Database::getPlayerArmies($gameId, $playerId, $db);
        if (empty($castles) && empty($armies)) {
            return array('gameover' => 1);
        } else {
            $costs = Cli_Model_Database::calculateCostsOfSoldiers($gameId, $playerId, $db);
            $income += Cli_Model_Database::calculateIncomeFromTowers($gameId, $playerId, $db);
            $gold = $gold + $income - $costs;
            Cli_Model_Database::updatePlayerInGameGold($gameId, $playerId, $gold, $db);

            return array(
                'gold' => $gold,
                'costs' => $costs,
                'income' => $income,
                'armies' => $armies,
//                'castles' => $castles,
                'color' => Cli_Model_Database::getColorByPlayerId($gameId, $playerId, $db)
            );
        }
    }

}
