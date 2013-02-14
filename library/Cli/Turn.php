<?php

class Cli_Turn {

    static public function next($gameId, $playerId, $db) {
        if (Cli_Database::playerLost($gameId, $playerId, $db)) {

            return;
        }

        $response = array();
        $nextPlayer = array(
            'color' => Cli_Database::getColorByPlayerId($gameId, $playerId, $db)
        );

        while (empty($response))
        {
            $nextPlayer = Cli_Database::getExpectedNextTurnPlayer($gameId, $nextPlayer['color'], $db);
            $playerCastlesExists = Cli_Database::playerCastlesExists($gameId, $nextPlayer['playerId'], $db);
            $playerArmiesExists = Cli_Database::playerArmiesExists($gameId, $nextPlayer['playerId'], $db);
            if ($playerCastlesExists || $playerArmiesExists) {
                $response['color'] = $nextPlayer['color'];

                if ($nextPlayer['playerId'] == $playerId) { // następny gracz to ten sam gracz, który zainicjował zmianę tury
                    $response['win'] = true;
                    Cli_Database::endGame($gameId, $db); // koniec gry
                } else { // zmieniam turę
                    Cli_Database::updateTurnNumber($gameId, $nextPlayer['playerId'], $db);
                    Cli_Database::raiseAllCastlesProductionTurn($gameId, $playerId, $db);
                    $turn = Cli_Database::getTurn($gameId, $db);
                    $response['lost'] = $turn['lost'];
                    $response['nr'] = $turn['nr'];
                }
            } else {
                Cli_Database::setPlayerLostGame($gameId, $nextPlayer['playerId'], $db);
            }
        }

        return $response;
    }

    static public function start($gameId, $playerId, $db = null) {
        if (!$db) {
            $db = self::getDb();
        }
        $castles = array();
        $income = 0;
        $costs = 0;

        Cli_Database::turnActivate($gameId, $playerId, $db);
        Cli_Database::resetHeroesMovesLeft($gameId, $playerId, $db);
        Cli_Database::resetSoldiersMovesLeft($gameId, $playerId, $db);

        $gold = Cli_Database::getPlayerInGameGold($gameId, $playerId, $db);

        if (Cli_Database::getTurnNumber($gameId, $db) > 0) {
            $castlesId = Cli_Database::getPlayerCastles($gameId, $playerId, $db);
            foreach ($castlesId as $id)
            {
                $castleId = $id['castleId'];
                $castles[$castleId] = Application_Model_Board::getCastle($castleId);
                $castle = $castles[$castleId];
                $income += $castle['income'];
                $armyId = Cli_Database::getArmyIdFromPosition($gameId, $castle['position'], $db);
                if (!$armyId) {
                    $armyId = Cli_Database::createArmy($gameId, $db, $castle['position'], $playerId);
                }
                if (!empty($armyId)) {
                    $castleProduction = Cli_Database::getCastleProduction($gameId, $castleId, $playerId, $db);
                    $castles[$castleId]['productionTurn'] = $castleProduction['productionTurn'];
                    $unitName = Application_Model_Board::getUnitName($castleProduction['production']);
                    if ($castleProduction['production'] AND
                            $castle['production'][$unitName]['time'] <= $castleProduction['productionTurn']
                            AND $castle['production'][$unitName]['cost'] <= $gold
                    ) {
                        if (Cli_Database::resetProductionTurn($gameId, $castleId, $playerId, $db) == 1) {
                            Cli_Database::addSoldierToArmy($gameId, $armyId, $castleProduction['production'], $db);
                        }
                    }
                }
            }
        }
        $armies = Cli_Database::getPlayerArmies($gameId, $playerId, $db);
        if (empty($castles) && empty($armies)) {
            return array('gameover' => 1);
        } else {
            $array = array();
            foreach ($armies as $army)
            {
                foreach ($army['soldiers'] as $unit)
                {
                    $costs += $unit['cost'];
                }
                $array['army' . $army['armyId']] = $army;
            }
            $gold = $gold + $income - $costs;
            Cli_Database::updatePlayerInGameGold($gameId, $playerId, $gold, $db);

            return array(
                'gold' => $gold,
                'costs' => $costs,
                'income' => $income,
                'armies' => $array,
                'castles' => $castles,
                'color' => Cli_Database::getColorByPlayerId($gameId, $playerId, $db)
            );
        }
    }

}
