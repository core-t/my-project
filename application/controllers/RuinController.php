<?php

class RuinController extends Game_Controller_Ajax {

    public function searchAction() {
        $armyId = $this->_request->getParam('aid');
        if (Zend_Validate::is($armyId, 'Digits')) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $heroId = $modelArmy->getHeroIdByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
            if (empty($heroId)) {
                throw new Exception('Brak heroId. Tylko Hero może przeszukiwać ruiny!');
            }
            $position = $modelArmy->getArmyPositionByArmyId($armyId, $this->_namespace->player['playerId']);
            $ruinId = Application_Model_Board::confirmRuinPosition($position);
            if (Zend_Validate::is($ruinId, 'Digits')) {
                $modelRuin = new Application_Model_Ruin($this->_namespace->gameId);
                if ($modelRuin->ruinExists($ruinId)) {
                    $response = $modelArmy->getArmyById($armyId);
                    $response['find'] = array('null', 1);
                    $response['ruinId'] = $ruinId;
                    echo Zend_Json::encode($response);
                    return null;
//                    throw new Exception('Ruiny są już przeszukane. '.$ruinId.' '.$armyId);
                }
                $find = $modelRuin->searchRuin($heroId, $armyId, $this->_namespace->player['playerId']);
                $response = $modelArmy->getArmyById($armyId);
                $response['find'] = $find;
                $response['ruinId'] = $ruinId;
                echo Zend_Json::encode($response);

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
            echo Zend_Json::encode(array('empty' => true));
        } else {
            throw new Exception('Brak "ruinId"!');
        }
    }

}
