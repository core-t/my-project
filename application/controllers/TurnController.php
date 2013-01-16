<?php

class TurnController extends Game_Controller_Ajax {

    public function getAction() {
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
//        if ($modelGame->playerLost($this->_namespace->player['playerId'])) {
//            echo Zend_Json::encode(array('lost' => 1));
//            return null;
//        }
        echo Zend_Json::encode($modelGame->getTurn());
    }

    public function startAction() {
        $mGame = new Application_Model_Game($this->_namespace->gameId);
        if (!$mGame->isPlayerTurn($this->_namespace->player['playerId'])) {
            throw new Exception('To nie jest moja tura.');
            return false;
        }
        if ($mGame->playerTurnActive($this->_namespace->player['playerId'])) {
            throw new Exception('Tura jest już aktywna. Próba ponownego aktywowania tury i wygenerowania produkcji.');
        }
        $mGame->turnActivate($this->_namespace->player['playerId']);
        $modelArmy = new Application_Model_Army($this->_namespace->gameId);
        $castles = array();
        $modelArmy->resetHeroesMovesLeft($this->_namespace->player['playerId']);
        $modelArmy->resetSoldiersMovesLeft($this->_namespace->player['playerId']);
        $gold = $mGame->getPlayerInGameGold($this->_namespace->player['playerId']);
        $income = 0;
        $costs = 0;
        if ($mGame->getTurnNumber() > 0) {
            $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
            $castlesId = $modelCastle->getPlayerCastles($this->_namespace->player['playerId']);
            foreach ($castlesId as $id)
            {
                $castleId = $id['castleId'];
                $castles[$castleId] = Application_Model_Board::getCastle($castleId);
                $castle = $castles[$castleId];
                $income += $castle['income'];
                $armyId = $modelArmy->getArmyIdFromPosition($castle['position']);
                if (!$armyId) {
                    $armyId = $modelArmy->createArmy($castle['position'], $this->_namespace->player['playerId']);
                }
                if (!empty($armyId)) {
                    $castleProduction = $modelCastle->getCastleProduction($castleId, $this->_namespace->player['playerId']);
                    $castles[$castleId]['productionTurn'] = $castleProduction['productionTurn'];
                    $unitName = Application_Model_Board::getUnitName($castleProduction['production']);
                    if ($castleProduction['production'] AND
                            $castle['production'][$unitName]['time'] <= $castleProduction['productionTurn']
                            AND $castle['production'][$unitName]['cost'] <= $gold
                    ) {
                        if ($modelCastle->resetProductionTurn($castleId, $this->_namespace->player['playerId']) == 1) {
                            $modelArmy->addSoldierToArmy($armyId, $castleProduction['production'], $this->_namespace->player['playerId']);
                        }
                    }
                }
            }
        }
        $armies = $modelArmy->getPlayerArmies($this->_namespace->player['playerId']);
        if (empty($castles) && empty($armies)) {
            echo Zend_Json::encode(array('gameover' => 1));
        } else {
            $array = array();
            foreach ($armies as $k => $army)
            {
                foreach ($army['soldiers'] as $unit)
                {
                    $costs += $unit['cost'];
                }
                $array['army' . $army['armyId']] = $army;
            }
            $gold = $gold + $income - $costs;
            $mGame->updatePlayerInGameGold($this->_namespace->player['playerId'], $gold);
            $resutl = array(
                'gold' => $gold,
                'costs' => $costs,
                'income' => $income,
                'armies' => $array,
                'castles' => $castles,
                'gameover' => 0
            );
            echo Zend_Json::encode($resutl);

//            $mWebSocket = new Application_Model_WebSocket();
//            $mWebSocket->authorizeChannel($mGame->getKeys());
//            $color = $mGame->getPlayerColor($this->_namespace->player['playerId']);
//            $mWebSocket->publishChannel($this->_namespace->gameId, $color . '.A.' . $color);
//            $mWebSocket->close();
        }
    }

}
