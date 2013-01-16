<?php

class GameajaxController extends Game_Controller_Ajax {

    public function _init() {

    }

    public function addarmyAction() {
        $armyId = $this->_request->getParam('armyId');
        if (!empty($armyId)) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $army = $modelArmy->getArmyById($armyId);
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $army['color'] = $modelGame->getPlayerColor($army['playerId']);
            echo Zend_Json::encode($army);
        } else {
            throw new Exception('Brak "armyId"!');
        }
    }

    public function armiesAction() {
        $color = $this->_request->getParam('color');
        if (!empty($color)) {
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $playerId = $modelGame->getPlayerIdByColor($color);
            if (!empty($playerId)) {
                $modelArmy = new Application_Model_Army($this->_namespace->gameId);
                echo Zend_Json::encode($modelArmy->getPlayerArmies($playerId));
            } else {
                throw new Exception('Brak $playerId!');
            }
        } else {
            throw new Exception('Brak "color"!');
        }
    }

    public function splitAction() {
        $armyId = $this->_request->getParam('aid');
        $h = $this->_request->getParam('h');
        $s = $this->_request->getParam('s');
        if (!empty($armyId) && (!empty($h) || !empty($s))) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $armyId = $modelArmy->splitArmy($h, $s, $armyId, $this->_namespace->player['playerId']);
            echo Zend_Json::encode($modelArmy->getArmyById($armyId));
        } else {
            throw new Exception('Brak "armyId", "s"!');
        }
    }

    public function joinAction() {
        $armyId1 = $this->_request->getParam('aid1');
        $armyId2 = $this->_request->getParam('aid2');
        if (!empty($armyId1) && !empty($armyId2)) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $position1 = $modelArmy->getArmyPositionByArmyId($armyId1, $this->_namespace->player['playerId']);
            $position2 = $modelArmy->getArmyPositionByArmyId($armyId2, $this->_namespace->player['playerId']);
            if (!empty($position1['x']) && !empty($position1['y']) && ($position1['x'] == $position2['x']) && ($position1['y'] == $position2['y'])) {
                $armyId = $modelArmy->joinArmiesAtPosition($position1, $this->_namespace->player['playerId']);
                echo Zend_Json::encode($modelArmy->getArmyById($armyId));
            } else {
                throw new Exception('Armie nie są na tej samej pozycji!');
            }
        } else {
            throw new Exception('Brak "armyId"!');
        }
    }

    public function disbandAction() {
        $armyId = $this->_request->getParam('aid');
        if (!empty($armyId)) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $response = $modelArmy->destroyArmy($armyId, $this->_namespace->player['playerId']);
            echo Zend_Json::encode($response);
        } else {
            throw new Exception('Brak "armyId"!');
        }
    }

    public function resurrectionAction() {
        $castleId = $this->_request->getParam('cid');
        if ($castleId != null) {
            $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
            if (!$modelCastle->isPlayerCastle($castleId, $this->_namespace->player['playerId'])) {
                throw new Exception('To nie jest Twój zamek!');
            }
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            if (!$modelArmy->isHeroInGame($this->_namespace->player['playerId'])) {
                $modelArmy->connectHero($this->_namespace->player['playerId']);
            }
            $heroId = $modelArmy->getDeadHeroId($this->_namespace->player['playerId']);
            if (!$heroId) {
                throw new Exception('Twój heros żyje!');
            }
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $gold = $modelGame->getPlayerInGameGold($this->_namespace->player['playerId']);
            if ($gold < 100) {
                throw new Exception('Za mało złota!');
            }
            $position = Application_Model_Board::getCastlePosition($castleId);
            $armyId = $modelArmy->heroResurection($heroId, $position, $this->_namespace->player['playerId']);
            $response = $modelArmy->getArmyById($armyId);
            $response['gold'] = $gold - 100;
            $modelGame->updatePlayerInGameGold($this->_namespace->player['playerId'], $response['gold']);
            echo Zend_Json::encode($response);
        } else {
            throw new Exception('Brak "castleId"!');
        }
    }

}
