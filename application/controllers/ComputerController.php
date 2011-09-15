<?php

class ComputerController extends Game_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function indexAction()
    {
        // action body
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        if(!$modelGame->isGameMaster($this->_namespace->player['playerId'])){
            throw new Exception('Nie Twoja gra!');
        }
        $playerId = getTurnPlayerId();
        if($modelGame->playerTurnActive($playerId)) {
            throw new Exception('Tura jest już aktywna. Próba ponownego aktywowania tury i wygenerowania produkcji.');
        }
        $modelPlayer = new Application_Model_Player(null, false);
        if(!$modelPlayer->isComputer($playerId)){
            throw new Exception('To nie komputer!');
        }
        $modelGame->turnActivate($playerId);
        $modelArmy = new Application_Model_Army($this->_namespace->gameId);
        $modelBoard = new Application_Model_Board();
        $castles = array();
        $modelArmy->resetHeroesMovesLeft($playerId);
        $modelArmy->resetSoldiersMovesLeft($playerId);
        $gold = $modelGame->getPlayerInGameGold($playerId);
        $income = 0;
        $costs = 0;
        if($modelGame->getTurnNumber() > 0) {
            $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
            $castlesId = $modelCastle->getPlayerCastles($playerId);
            foreach($castlesId as $id) {
                $castleId = $id['castleId'];
                $castles[$castleId] = $modelBoard->getCastle($castleId);
                $castle = $castles[$castleId];
                $income += $castle['income'];
                $armyId = $modelArmy->getArmyIdFromPosition($castle['position']);
                if (!$armyId) {
                    $armyId = $modelArmy->createArmy($castle['position'], $playerId);
                }
                if (!empty($armyId)) {
                    $castleProduction = $modelCastle->getCastleProduction($castleId, $playerId);
                    $castles[$castleId]['productionTurn'] = $castleProduction['productionTurn'];
                    $unitName = $modelBoard->getUnitName($castleProduction['production']);
                    if($castleProduction['production'] AND
                    $castle['production'][$unitName]['time'] <= $castleProduction['productionTurn']
                    AND $castle['production'][$unitName]['cost'] <= $gold
                    ) {
                        if($modelCastle->resetProductionTurn($castleId, $playerId) == 1) {
                            $modelArmy->addSoldierToArmy($armyId, $castleProduction['production'], $playerId);
                        }
                    }
                }
            }
        }
        $armies = $modelArmy->getPlayerArmies($playerId);
        if(empty($castles) && empty($armies)){
            $this->view->response = Zend_Json::encode(array('gameover'=>1));
        }else{
            $array = array();
            $resutl = array();
            foreach ($armies as $k => $army) {
                foreach($army['soldiers'] as $unit){
                    $costs += $unit['cost'];
                }
                $array['army'.$army['armyId']] = $army;
            }
            $gold = $gold + $income - $costs;
            $modelGame->updatePlayerInGameGold($playerId, $gold);

            $this->view->response = Zend_Json::encode($resutl);
        }
    }


}

