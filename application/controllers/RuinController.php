<?php

class RuinController extends Game_Controller_Action {

    public function _init() {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function searchAction() {
        $armyId = $this->_request->getParam('aid');
        if (!empty($armyId)) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $heroId = $modelArmy->getHeroIdByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
            if (empty($heroId)) {
                throw new Exception('Brak heroId. Tylko Hero może przeszukiwać ruiny!');
            }
            $position = $modelArmy->getArmyPositionByArmyId($armyId, $this->_namespace->player['playerId']);
            $ruinId = Application_Model_Board::confirmRuinPosition($position);
            if ($ruinId !== null) {
                $modelRuin = new Application_Model_Ruin($this->_namespace->gameId);
                if ($modelRuin->ruinExists($ruinId)) {
                    $response = $modelArmy->getArmyById($armyId);
                    $response['find'] = 'empty';
                    $response['ruinId'] = $ruinId;
                    $this->view->response = Zend_Json::encode($response);
                    return null;
//                    throw new Exception('Ruiny są już przeszukane. '.$ruinId.' '.$armyId);
                }
                $modelRuin->addRuin($ruinId);
                $response = $modelArmy->getArmyById($armyId);
                $response['find'] = $modelRuin->searchRuin($modelArmy, $heroId, $armyId, $this->_namespace->player['playerId']);
                $response['ruinId'] = $ruinId;
                $this->view->response = Zend_Json::encode($response);
            } else {
                throw new Exception('Brak ruinId');
            }
        } else {
            throw new Exception('Brak "armyId"!');
        }
    }

    public function getAction() {
        $ruinId = $this->_request->getParam('rid');
        if ($ruinId !== null) {
            $modelRuin = new Application_Model_Ruin($this->_namespace->gameId);
            if ($modelRuin->ruinExists($ruinId)) {
                $ruin = array('empty' => true);
            } else {
                $ruin = array('empty' => false);
            }
            $this->view->response = Zend_Json::encode(array('empty' => true));
        } else {
            throw new Exception('Brak "ruinId"!');
        }
    }

}
