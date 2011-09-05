<?php

class RuinController extends Game_Controller_Action {

    public function _init() {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function searchAction(){
        $armyId = $this->_request->getParam('aid');
        if (!empty($armyId)) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $heroId = $modelArmy->getHeroIdByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
            if(empty($heroId)){
                throw new Exception('Brak heroId. Tylko Hero może przeszukiwać ruiny!');
            }
            $position = $modelArmy->getArmyPositionByArmyId($armyId, $this->_namespace->player['playerId']);
            $position = explode(',', substr($position['position'], 1 , -1));
            $ruinId = Application_Model_Board::confirmRuinPosition($position);
            if($ruinId !== null){
                $modelRuin = new Application_Model_Ruin($this->_namespace->gameId);
                if($modelRuin->ruinExists($ruinId)){
                    throw new Exception('Ruiny są już przeszukane. '.$ruinId.' '.$armyId);
                }
                $modelRuin->addRuin($ruinId);
                $modelGame = new Application_Model_Game($this->_namespace->gameId);
                $turn = $modelGame->getTurn();
                $random = rand(0,100);
                if($random < 10){//10%
                    //śmierć
                    $find = array('death',1);
                    $modelArmy->armyRemoveHero($heroId);
                }elseif($random < 55){//45%
                    //kasa
                    $gold = rand(500,1500);
                    $find = array('gold',$gold);
                    $modelGame = new Application_Model_Game($this->_namespace->gameId);
                    $inGameGold = $modelGame->getPlayerInGameGold($this->_namespace->player['playerId']);
                    $modelGame->updatePlayerInGameGold($this->_namespace->player['playerId'], $gold+$inGameGold);
                    $modelArmy->zeroHeroMovesLeft($armyId, $heroId, $this->_namespace->player['playerId']);
                }elseif($random < 85){//30%
                    //jednostki
                    if($turn['nr'] <= 7){
                        $max1 = 11;
                        $min2 = 1;
                        $max2 = 1;
                    }elseif($turn['nr'] <= 12){
                        $max1 = 13;
                        $min2 = 1;
                        $max2 = 1;
                    }elseif($turn['nr'] <= 16){
                        $max1 = 14;
                        $min2 = 1;
                        $max2 = 1;
                    }elseif($turn['nr'] <= 19){
                        $max1 = 15;
                        $min2 = 1;
                        $max2 = 1;
                    }elseif($turn['nr'] <= 21){
                        $max1 = 15;
                        $min2 = 1;
                        $max2 = 2;
                    }elseif($turn['nr'] <= 23){
                        $max1 = 15;
                        $min2 = 1;
                        $max2 = 3;
                    }elseif($turn['nr'] <= 25){
                        $max1 = 15;
                        $min2 = 2;
                        $max2 = 3;
                    }elseif($turn['nr'] <= 27){
                        $max1 = 15;
                        $min2 = 3;
                        $max2 = 3;
                    }
                    $unitId = rand(11,$max1);
                    $numerOfUnits = rand($min2,$max2);
                    $find = array('alies',$numerOfUnits);
                    for($i = 0; $i < $numerOfUnits; $i++){
                        $modelArmy->addSoldierToArmy($armyId, $unitId, $this->_namespace->player['playerId']);
                    }
                    $modelArmy->zeroHeroMovesLeft($armyId, $heroId, $this->_namespace->player['playerId']);
                }elseif($random < 95){//10%
                    //nic
                    $find = array('null',1);
                    $modelArmy->zeroHeroMovesLeft($armyId, $heroId, $this->_namespace->player['playerId']);
                }else{//5%
                    //artefakt
                    $find = array('artefact',1);
                    $modelArmy->zeroHeroMovesLeft($armyId, $heroId, $this->_namespace->player['playerId']);
                }
                $response = $modelArmy->getArmyById($armyId);
                $response['find'] = $find;
                $this->view->response = Zend_Json::encode($response);
            }else{
                throw new Exception('Brak ruinId');
            }
        } else {
            throw new Exception('Brak "armyId"!');
        }
    }

    public function getAction(){
        $ruinId = $this->_request->getParam('rid');
        if ($ruinId !== null) {
            $modelRuin = new Application_Model_Ruin($this->_namespace->gameId);
                if($modelRuin->ruinExists($ruinId)){
                    $ruin = array('empty'=>true);
                }else{
                    $ruin = array('empty'=>false);
                }
                $this->view->response = Zend_Json::encode(array('empty'=>true));
        } else {
            throw new Exception('Brak "ruinId"!');
        }
    }
}
