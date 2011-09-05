<?php

class GameajaxController extends Game_Controller_Action {

    public function _init() {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function addarmyAction() {
        $armyId = $this->_request->getParam('armyId');
        if (!empty($armyId)) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $army = $modelArmy->getArmyById($armyId);
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $army['color'] = $modelGame->getPlayerColor($army['playerId']);
            $this->view->response = Zend_Json::encode($army);
        } else {
            throw new Exception('Brak "armyId"!');
        }
    }

    public function armiesAction(){
        $color = $this->_request->getParam('color');
        if (!empty($color)) {
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $playerId = $modelGame->getPlayerIdByColor($color);
            if(!empty($playerId)){
                $modelArmy = new Application_Model_Army($this->_namespace->gameId);
                $this->view->response = Zend_Json::encode($modelArmy->getPlayerArmies($playerId));
            }else{
                throw new Exception('Brak $playerId!');
            }
        } else {
            throw new Exception('Brak "color"!');
        }
    }

    public function splitAction(){
        $armyId = $this->_request->getParam('aid');
        $h = $this->_request->getParam('h');
        $s = $this->_request->getParam('s');
        if (!empty($armyId) && (!empty($h) || !empty($s))) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $armyId = $modelArmy->splitArmy($h, $s, $armyId, $this->_namespace->player['playerId']);
            $this->view->response = Zend_Json::encode($modelArmy->getArmyById($armyId));
        } else {
            throw new Exception('Brak "armyId", "s"!');
        }
    }

    public function joinAction(){
        $armyId1 = $this->_request->getParam('aid1');
        $armyId2 = $this->_request->getParam('aid2');
        if (!empty($armyId1) && !empty($armyId2)) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $position1 = $modelArmy->getArmyPositionByArmyId($armyId1, $this->_namespace->player['playerId']);
            $position2 = $modelArmy->getArmyPositionByArmyId($armyId2, $this->_namespace->player['playerId']);
            if(!empty ($position1['position']) && ($position1['position'] == $position2['position'])){
                $position = substr($position1['position'], 1 , -1);
                $armyId = $modelArmy->joinArmiesAtPosition($position, $this->_namespace->player['playerId']);
                $this->view->response = Zend_Json::encode($modelArmy->getArmyById($armyId));
                
            }else{
                throw new Exception('Armie nie są na tej samej pozycji!');
            }
        } else {
            throw new Exception('Brak "armyId"!');
        }
    }

    public function disbandAction(){
        $armyId = $this->_request->getParam('aid');
        if (!empty($armyId)) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $response = $modelArmy->destroyArmy($armyId, $this->_namespace->player['playerId']);
            $this->view->response = Zend_Json::encode($response);
        } else {
            throw new Exception('Brak "armyId"!');
        }
    }

    public function resurrectionAction(){
        $castleId = $this->_request->getParam('cid');
        if ($castleId != null){
            $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
            if(!$modelCastle->isPlayerCastle($castleId, $this->_namespace->player['playerId'])){
                throw new Exception('To nie jest Twój zamek!');
            }
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            if(!$modelArmy->isHeroInGame($this->_namespace->player['playerId'])){
                $modelArmy->connectHero($this->_namespace->player['playerId']);
            }
            $heroId = $modelArmy->getDeadHeroId($this->_namespace->player['playerId']);
            if(!$heroId){
                throw new Exception('Twój heros żyje!');
            }
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $gold = $modelGame->getPlayerInGameGold($this->_namespace->player['playerId']);
            if($gold < 100){
                throw new Exception('Za mało złota!');
            }
            $modelBoard = new Application_Model_Board();
            $position = $modelBoard->getCastlePosition($castleId);
            $armyId = $modelArmy->heroResurection($heroId, $position, $this->_namespace->player['playerId']);
            $response = $modelArmy->getArmyById($armyId);
            $response['gold'] = $gold - 100;
            $modelGame->updatePlayerInGameGold($this->_namespace->player['playerId'], $response['gold']);
            $this->view->response = Zend_Json::encode($response);
        } else {
            throw new Exception('Brak "castleId"!');
        }
    }

    public function ruinsAction(){
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

}
