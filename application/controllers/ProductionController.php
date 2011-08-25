<?php

class ProductionController extends Game_Controller_Action
{

    public function _init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function setAction()
    {
        // action body
        $castleId = $this->_request->getParam('castleId');
        $unitId = $this->_request->getParam('unitId');
        if ($castleId != null AND $unitId != null) {
            if($unitId == -1) {
                $unitId = null;
            }
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            if($modelGame->isPlayerTurn($this->_namespace->player['playerId'])) {
                $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
                if(!$modelCastle->isPlayerCastle($castleId, $this->_namespace->player['playerId'])){
                    throw new Exception('To nie jest Twój zamek!');
                }
                $res = $modelCastle->setCastleProduction($castleId, $unitId, $this->_namespace->player['playerId']);
                switch ($res) {
                    case 1:
                        $this->view->response = Zend_Json::encode(array('set'=>true));
                        break;
                    case 0:
                        throw new Exception('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                        break;
                    case null:
                        throw new Exception('Zapytanie zwróciło błąd');
                        break;
                    default:
                        throw new Exception('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                        break;
                }
            }
        } else {
            throw new Exception('Brak "castleId"!');
        }
    }

    public function stopAction()
    {
        // action body
    }


}





