<?php

class CastleController extends Game_Controller_Action
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
                $response['color'] = $this->_namespace->player['color'];
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
        if(!$this->modelCastle->isPlayerCastle($this->castleId, $this->_namespace->player['playerId'])){
            throw new Exception('Nie Twój zamek.');
        }
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        $modelBoard = new Application_Model_Board();
        $gold = $modelGame->getPlayerInGameGold($this->_namespace->player['playerId']);
        $defenseModifier = $this->modelCastle->getCastleDefenseModifier($this->castleId);
        $defensePoints = $modelBoard->getCastleDefense($this->castleId);
        $defense = $defenseModifier + $defensePoints;
        $costs = 0;
        for($i = 1; $i <= $defense; $i++){
            $costs += $i*100;
        }
        if($gold < $costs){
            throw new Exception('Za mało złota!');
        }
        $res = $this->modelCastle->buildDefense($this->castleId, $this->_namespace->player['playerId']);
        switch ($res) {
            case 1:
                $response = $this->modelCastle->getCastle($this->castleId);
                $response['defensePoints'] = $defensePoints;
                $response['color'] = $this->_namespace->player['color'];
                $response['gold'] = $gold - $costs;
                $modelGame->updatePlayerInGameGold($this->_namespace->player['playerId'], $response['gold']);
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

    public function getAction()
    {
        // action body
        $castle = $this->modelCastle->getCastle($this->castleId);
        if(isset($castle['playerId'])){
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $modelBoard = new Application_Model_Board();
            $castle['defensePoints'] = $modelBoard->getCastleDefense($this->castleId);
            $castle['color'] = $modelGame->getPlayerColor($castle['playerId']);
            $this->view->response = Zend_Json::encode($castle);
        }
    }


}







