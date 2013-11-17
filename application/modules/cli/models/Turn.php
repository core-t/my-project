<?php

class Cli_Model_Turn
{
    private $_gameId;
    private $_db;

    public function __construct($gameId, $db, $gameHandler)
    {
        $this->_gameId = $gameId;
        $this->_db = $db;
        $this->_gameHandler = $gameHandler;
    }

    public function next($playerId)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_gameId, $this->_db);

        if ($mPlayersInGame->playerLost($playerId)) {
            return;
        }

        $playersInGameColors = Zend_Registry::get('playersInGameColors');
        $mArmy = new Application_Model_Army($this->_gameId, $this->_db);
        $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);

        $playerCastlesExists = $mCastlesInGame->playerCastlesExists($playerId);
        $playerArmiesExists = $mArmy->playerArmiesExists($playerId);
        if (!$playerCastlesExists || !$playerArmiesExists) {
            $token = array(
                'type' => 'dead',
                'color' => $playersInGameColors[$playerId]
            );
            $this->_gameHandler->sendToChannel($this->_db, $token, $this->_gameId);
            $mPlayersInGame->setPlayerLostGame($playerId);
            sleep(3);
        }

        $nextPlayerId = $playerId;
        $loop = null;

        $mGame = new Application_Model_Game($this->_gameId, $this->_db);

        while (empty($loop)) {
            $nextPlayerId = $this->getExpectedNextTurnPlayer($playersInGameColors[$nextPlayerId]);
            $playerCastlesExists = $mCastlesInGame->playerCastlesExists($nextPlayerId);
            $playerArmiesExists = $mArmy->playerArmiesExists($nextPlayerId);
            if ($playerCastlesExists || $playerArmiesExists) {
                if ($nextPlayerId == $playerId) { // następny gracz to ten sam gracz, który zainicjował zmianę tury
                    $loop = true;

                    $mGame->endGame(); // koniec gry
                    $this->saveResults();

                    $token = array(
                        'type' => 'end'
                    );

                    $this->_gameHandler->sendToChannel($this->_db, $token, $this->_gameId);
                } else { // zmieniam turę
                    $loop = true;

                    $mGame->updateTurnNumber($nextPlayerId, $playersInGameColors[$nextPlayerId]);
                    $mCastlesInGame->increaseAllCastlesProductionTurn($playerId);

                    $turnNumber = $mGame->getTurnNumber();

                    $token = array(
                        'type' => 'nextTurn',
                        'nr' => $turnNumber,
                        'color' => $playersInGameColors[$nextPlayerId]
                    );
                    $mTurnHistory = new Application_Model_TurnHistory($this->_gameId, $this->_db);
                    $mTurnHistory->add($nextPlayerId, $token['nr']);

                    $this->_gameHandler->sendToChannel($this->_db, $token, $this->_gameId);
                }
            } else {
                $token = array(
                    'type' => 'dead',
                    'color' => $playersInGameColors[$nextPlayerId]
                );
                $this->_gameHandler->sendToChannel($this->_db, $token, $this->_gameId);
                $mPlayersInGame->setPlayerLostGame($nextPlayerId);
                sleep(3);
            }
        }
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
            $type = 'computerStart';
        } else {
            $type = 'startTurn';
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

        $costs = $mSoldier->calculateCostsOfSoldiers($mArmy->getSelectForPlayerAll($playerId));
        $mTowersInGame = new Application_Model_TowersInGame($this->_gameId, $this->_db);
        $income += $mTowersInGame->calculateIncomeFromTowers($playerId);
        $gold = $gold + $income - $costs;

        $mPlayersInGame->updatePlayerGold($playerId, $gold);

        $playersInGameColors = Zend_Registry::get('playersInGameColors');

        $token = array(
            'type' => $type,
            'gold' => $gold,
            'costs' => $costs,
            'income' => $income,
            'armies' => $armies,
            'castles' => $castlesInGame,
            'color' => $playersInGameColors[$playerId]
        );
        $this->_gameHandler->sendToChannel($this->_db, $token, $this->_gameId);
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
        foreach ($playersInGame as $player) {
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
                    } else {
                        $nextPlayerId = $player['playerId'];
                    }
                    break;
                }
            }
        }

        if (!isset($nextPlayerId)) {
            $l = new Coret_Model_Logger('cli');
            $l->log('Błąd! Nie znalazłem gracza');

            return;
        }

        return $nextPlayerId;
    }

    public function saveResults()
    {
        $mGameScore = new Application_Model_GameScore($this->_gameId, $this->_db);

        if ($mGameScore->gameScoreExists()) {
            return;
        }

        $mGameResults = new Application_Model_GameResults($this->_gameId, $this->_db);
        $mPlayer = new Application_Model_Player($this->_db);

        $mCastlesConquered = new Application_Model_CastlesConquered($this->_gameId, $this->_db);
        $mCastlesDestroyed = new Application_Model_CastlesDestroyed($this->_gameId, $this->_db);
        $mHeroesKilled = new Application_Model_HeroesKilled($this->_gameId, $this->_db);
        $mSoldiersKilled = new Application_Model_SoldiersKilled($this->_gameId, $this->_db);
        $mSoldiersCreated = new Application_Model_SoldiersCreated($this->_gameId, $this->_db);
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_gameId, $this->_db);
        $mUnitsInGame = new Application_Model_UnitsInGame($this->_gameId, $this->_db);
        $mHeroesInGame = new Application_Model_HeroesInGame($this->_gameId, $this->_db);
        $mCastlesInGame = new Application_Model_CastlesInGame($this->_gameId, $this->_db);

        $playersInGameColors = Zend_Registry::get('playersInGameColors');
        $units = Zend_Registry::get('units');

        $castlesConquered = $mCastlesConquered->countConquered($playersInGameColors);
        $castlesLost = $mCastlesConquered->countLost($playersInGameColors);

        $heroesKilled = $mHeroesKilled->countKilled($playersInGameColors);
        $heroesLost = $mHeroesKilled->countLost($playersInGameColors);


        $soldiersKilled = $mSoldiersKilled->countKilled($playersInGameColors);
        $soldiersLost = $mSoldiersKilled->countLost($playersInGameColors);

        $soldiersCreated = $mSoldiersCreated->getCreated();

        $castlesDestroyed = $mCastlesDestroyed->countAll($playersInGameColors);

        $playersGold = $mPlayersInGame->getAllPlayersGold();

        foreach ($playersInGameColors as $playerId => $shortName) {
            $points = array();
            $sumPoints = 0;

            if (isset($castlesConquered[$shortName])) {
                $playerCastlesConquered = $castlesConquered[$shortName];
            } else {
                $playerCastlesConquered = 0;
            }

            $points['castlesConquered'] = $playerCastlesConquered * 100;
            $sumPoints += $points['castlesConquered'];

            if (isset($castlesLost[$shortName])) {
                $playerCastlesLost = $castlesLost[$shortName];
            } else {
                $playerCastlesLost = 0;
            }

            $points['castlesLost'] = -($playerCastlesLost * 100);
            $sumPoints += $points['castlesLost'];

            if (isset($castlesDestroyed[$shortName])) {
                $playerCastlesDestroyed = $castlesDestroyed[$shortName];
            } else {
                $playerCastlesDestroyed = 0;
            }

            $points['castlesDestroyed'] = -($playerCastlesDestroyed * 100);
            $sumPoints += $points['castlesDestroyed'];

            $playerSoldiersCreated = 0;
            $points['soldiersCreated'] = 0;
            if (isset($soldiersCreated[$playerId])) {
                foreach ($soldiersCreated[$playerId] as $unitId) {
                    $playerSoldiersCreated++;
                    $points['soldiersCreated'] += $units[$unitId]['attackPoints'] + $units[$unitId]['defensePoints'];
                }
            }
            $sumPoints += $points['soldiersCreated'];

            $playerSoldiersKilled = 0;
            $points['soldiersKilled'] = 0;
            if (isset($soldiersKilled[$playerId])) {
                foreach ($soldiersKilled[$playerId] as $unitId) {
                    $playerSoldiersKilled++;
                    $points['soldiersKilled'] += $units[$unitId]['attackPoints'] + $units[$unitId]['defensePoints'];
                }
            }
            $sumPoints += $points['soldiersKilled'];

            $playerSoldiersLost = 0;
            $points['soldiersLost'] = 0;
            if (isset($soldiersLost[$playerId])) {
                foreach ($soldiersLost[$playerId] as $unitId) {
                    $playerSoldiersLost++;
                    $points['soldiersLost'] -= $units[$unitId]['attackPoints'];
                }
            }

            if (isset($heroesKilled[$shortName])) {
                $playerHeroesKilled = $heroesKilled[$shortName];
            } else {
                $playerHeroesKilled = 0;
            }

            $points['heroesKilled'] = $playerHeroesKilled * 10;
            $sumPoints += $points['heroesKilled'];

            if (isset($heroesLost[$shortName])) {
                $playerHeroesLost = $heroesLost[$shortName];
            } else {
                $playerHeroesLost = 0;
            }

            $points['heroesLost'] = -($playerHeroesLost * 10);
            $sumPoints += $points['heroesLost'];

            $points['gold'] = $playersGold[$playerId];
            $sumPoints += $points['gold'];
            $points['score'] = $sumPoints;

            $mGameResults->add(
                $playerId,
                $playerCastlesConquered,
                $playerCastlesLost,
                $playerCastlesDestroyed,
                $playerSoldiersCreated,
                $playerSoldiersKilled,
                $playerSoldiersLost,
                $playerHeroesKilled,
                $playerHeroesLost,
                $playersGold[$playerId],
                0, 0, 0
            );

            $mGameScore->add($playerId, $points);

            $mPlayer->addScore($playerId, $sumPoints);
        }

    }
}
