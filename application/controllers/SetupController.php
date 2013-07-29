<?php

class SetupController extends Game_Controller_Gui
{

    public function _init()
    {
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/playerslist.css?v=' . Zend_Registry::get('config')->version);
        $this->view->Websocket();
    }

    public function indexAction()
    {
        $this->view->headScript()->appendFile('/js/gamesetup.js?v=' . Zend_Registry::get('config')->version);
        $gameId = $this->_request->getParam('gameId');
        if (empty($gameId)) {
            throw new Exception('Brak gameId!');
        }
        if (isset($this->_namespace->armyId)) {
            unset($this->_namespace->armyId);
        }

        $this->_namespace->gameId = $gameId; // zapisuję gemeId do sesji

        $modelGame = new Application_Model_Game($gameId);
        $modelGame->updateGameMaster($this->_namespace->player['playerId']);

        if ($modelGame->getGameMasterId() != $this->_namespace->player['playerId']) {
            if ($modelGame->isPlayerInGame($this->_namespace->player['playerId'])) {
                $modelGame->disconnectFromGame($gameId, $this->_namespace->player['playerId']);
            }
            $modelGame->joinGame($this->_namespace->player['playerId']);
        } elseif (!$modelGame->isPlayerInGame($this->_namespace->player['playerId'])) {
            $modelGame->joinGame($this->_namespace->player['playerId']);
        }

        $this->view->colors = $modelGame->getAllColors();
        $this->view->numberOfPlayers = $modelGame->getNumberOfPlayers();
        $this->view->accessKey = $modelGame->getAccessKey($this->_namespace->player['playerId']);
        $this->view->gameId = $gameId;
        $this->view->player = $this->_namespace->player;
    }

    public function startAction()
    {
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak gameId!');
        }
        if ($this->_namespace->armyId) {
            throw new Exception('Jest już armyId!');
        }
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        if (!$modelGame->isPlayerReady($this->_namespace->player['playerId'])) {
            $this->_redirect('/' . Zend_Registry::get('lang') . '/new');
        }

        $modelArmy = new Application_Model_Army($this->_namespace->gameId);
        $modelHero = new Application_Model_Hero($this->_namespace->player['playerId']);
        $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
        $mMapCastles = new Application_Model_MapCastles($modelGame->getMapId());

        $startPositions = $mMapCastles->getDefaultStartPositions();
        $playerHeroes = $modelHero->getHeroes();

        $this->_namespace->player['color'] = $modelGame->getPlayerColor($this->_namespace->player['playerId']);

        if (empty($playerHeroes)) {
            $modelHero->createHero();
            $playerHeroes = $modelHero->getHeroes();
        }

        $armyId = $modelArmy->createArmy(
            $startPositions[$this->_namespace->player['color']]['position'], $this->_namespace->player['playerId']);

        $res = $modelArmy->addHeroToGame($armyId, $playerHeroes[0]['heroId']);
        switch ($res) {
            case 1:
                $modelCastle->addCastle($startPositions[$this->_namespace->player['color']]['id'], $this->_namespace->player['playerId']);
                $this->_namespace->armyId = $armyId;
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

}

