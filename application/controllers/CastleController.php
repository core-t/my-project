<?php

class CastleController extends Warlords_Controller_Action
{
    private $castleId;
    private $modelCastle;
    public function _init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
        $this->castleId = $this->_request->getParam('cid');
        if ($this->castleId == null) {
            throw new Exception('Brak "castleId"!');
        }
        $this->modelCastle = new Application_Model_Castle($this->_namespace->gameId);
    }

    public function razeAction()
    {
        // action body
        $res = $this->modelCastle->razeCastle($this->castleId, $this->_namespace->player['playerId']);
        switch ($res) {
            case 1:
                $modelGame = new Application_Model_Game($this->_namespace->gameId);
                $gold = $modelGame->getPlayerInGameGold($this->_namespace->player['playerId']) + 1000;
                $modelGame->updatePlayerInGameGold($this->_namespace->player['playerId'], $gold);
                $response = $this->modelCastle->getCastle($this->castleId);
                $response['gold'] = $gold;
                $this->view->response = Zend_Json::encode($response);
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

    public function buildAction()
    {
        // action body
        $res = $this->modelCastle->buildCastle($this->castleId, $this->_namespace->player['playerId']);
        switch ($res) {
            case 1:
                $this->view->response = Zend_Json::encode($this->modelCastle->getCastle($this->castleId));
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

    public function getAction()
    {
        // action body
        $castle = $this->modelCastle->getCastle($this->castleId);
        if(isset($castle['playerId'])){
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $castle['color'] = $modelGame->getPlayerColor($castle['playerId']);
            $this->view->response = Zend_Json::encode($castle);
        }
    }


}







