<?php

class Cli_Model_Turn
{
    public function __construct($gameId, $db)
    {
        $this->_gameId = $gameId;
        $this->_db = $db;
    }

    public function next($playerId)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_gameId, $this->_db);

        if ($mPlayersInGame->playerLost($playerId)) {
            return;
        }

        $response = array();

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $nextPlayer = array(
            'color' => $playersInGameColors[$playerId]
        );

        $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);
        $mGame = new Application_Model_Game($this->_gameId, $this->_db);
        $mArmy = new Application_Model_Army($this->_gameId, $this->_db);

        while (empty($response)) {
            $nextPlayer = $this->getExpectedNextTurnPlayer($nextPlayer['color']);
            $playerCastlesExists = $mCastlesInGame->playerCastlesExists($nextPlayer['playerId']);
            $playerArmiesExists = $mArmy->playerArmiesExists($nextPlayer['playerId']);
            if ($playerCastlesExists || $playerArmiesExists) {
                $response['color'] = $nextPlayer['color'];

                if ($nextPlayer['playerId'] == $playerId) { // następny gracz to ten sam gracz, który zainicjował zmianę tury
                    $response['win'] = true;
                    $mGame->endGame(); // koniec gry

                    $this->saveResults();
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

        $mTurnHistory = new Application_Model_TurnHistory($this->_gameId, $this->_db);
        $mTurnHistory->add($nextPlayer['playerId'], $response['nr']);

        return $response;
    }


    public function start($playerId, $computer = null)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_gameId, $this->_db);
        $mPlayersInGame->turnActivate($playerId);

        $mArmy = new Application_Model_Army($this->_gameId, $this->_db);
        $mSoldier = new Application_Model_UnitsInGame($this->_gameId, $this->_db);
        $mSoldier->resetMovesLeft($mArmy->getSelectForPlayerAll($playerId));

        $gold = $mPlayersInGame->getPlayerGold($playerId);
        if ($computer) {
            $mArmy->unfortifyComputerArmies($playerId);
        }
        $mHeroesInGame = new Application_Model_HeroesInGame($this->_gameId, $this->_db);
        $mHeroesInGame->resetHeroesMovesLeft($playerId);

        $income = 0;
        $color = null;

        $mapCastles = Zend_Registry::get('castles');

        $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);
        $castlesInGame = $mCastlesInGame->getPlayerCastles($playerId);

        $mSoldier = new Application_Model_UnitsInGame($this->_gameId, $this->_db);
        $mSoldiersCreated = new Application_Model_SoldiersCreated($this->_gameId, $this->_db);

        foreach ($castlesInGame as $castleId => $castleInGame) {
            $income += $mapCastles[$castleId]['income'];

            $castleProduction = $mCastlesInGame->getProduction($castleId, $playerId);

            if ($computer) {
                if (isset($mapCastles[$castleId]['position'])) {
                    $gold = Cli_Model_ComputerMainBlocks::handleHeroResurrection($this->_gameId, $gold, $mapCastles[$castleId]['position'], $playerId, $this->_db);
                }

                $mGame = new Application_Model_Game($this->_gameId, $this->_db);
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
                    $mSoldier->add($armyId, $unitId);
                    $mSoldiersCreated->add($unitId, $playerId);
                }
            }
        }

        $armies = $mArmy->getPlayerArmiesWithUnits($playerId);

        if (empty($castlesInGame) && empty($armies)) {
            return array('action' => 'gameover');
        } else {
            $costs = $mSoldier->calculateCostsOfSoldiers($mArmy->getSelectForPlayerAll($playerId));
            $mTowersInGame = new Application_Model_TowersInGame($this->_gameId, $this->_db);
            $income += $mTowersInGame->calculateIncomeFromTowers($playerId);
            $gold = $gold + $income - $costs;

            $mPlayersInGame->updatePlayerGold($playerId, $gold);

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

    public function getExpectedNextTurnPlayer($playerColor)
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

        $mPlayersInGame = new Application_Model_PlayersInGame($this->_gameId, $this->_db);
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

    public function saveResults()
    {
        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $mGameResults = new Application_Model_GameResults($this->_gameId, $this->_db);
        $mCastlesConquered = new Application_Model_CastlesConquered($this->_gameId, $this->_db);
        $mCastlesDestroyed = new Application_Model_CastlesDestroyed($this->_gameId, $this->_db);
        $mHeroesKilled = new Application_Model_HeroesKilled($this->_gameId, $this->_db);
        $mSoldiersKilled = new Application_Model_SoldiersKilled($this->_gameId, $this->_db);
        $mSoldiersCreated = new Application_Model_SoldiersCreated($this->_gameId, $this->_db);
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_gameId, $this->_db);
        $mUnitsInGame = new Application_Model_UnitsInGame($this->_gameId, $this->_db);
        $mHeroesInGame = new Application_Model_HeroesInGame($this->_gameId, $this->_db);
        $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);

        $castlesConquered = array(
            'winners' => $mCastlesConquered->countConquered($playersInGameColors),
            'losers' => $mCastlesConquered->countLost($playersInGameColors)
        );

        $heroesKilled = array(
            'winners' => $mHeroesKilled->countKilled($playersInGameColors),
            'losers' => $mHeroesKilled->countLost($playersInGameColors)
        );

        $soldiersKilled = array(
            'winners' => $mSoldiersKilled->countKilled($playersInGameColors),
            'losers' => $mSoldiersKilled->countLost($playersInGameColors)
        );

        $soldiersCreated = $mSoldiersCreated->countCreated($playersInGameColors);

        $castlesDestroyed = $mCastlesDestroyed->countAll($playersInGameColors);

        $playersGold = $mPlayersInGame->getAllPlayersGold();

        foreach ($playersInGameColors as $playerId => $shortName) {

            $mGameResults->add(
                $playerId,
                $castlesConquered['winners'][$playerId],
                $castlesConquered['losers'][$playerId],
                $castlesDestroyed[$playerId],
                $soldiersCreated[$playerId],
                $soldiersKilled['winners'][$playerId],
                $soldiersKilled['losers'][$playerId],
                $heroesKilled['winners'][$playerId],
                $heroesKilled['losers'][$playerId],
                $playersGold[$playerId], 0, 0, 0

            );
        }

    }
}
