<?php

class GamesetupController extends Game_Controller_Action {

    public function _init() {
        /* Initialize action controller here */
//        $this->view->headScript()->prependFile('https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/playerslist.css');
        $this->view->headScript()->prependFile($this->view->baseUrl() . '/js/jquery.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jWebSocket.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jwsChannelPlugIn.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/index.websocket.js');
        new Application_View_Helper_Logout($this->view, $this->_namespace->player);
        new Application_View_Helper_Menu($this->view, null);
        new Application_View_Helper_Websocket($this->view, null);
    }

    public function indexAction() {
        new Application_View_Helper_Logout($this->view, $this->_namespace->player);
        new Application_View_Helper_Menu($this->view, null);
        $this->view->headScript()->appendFile('/js/gamesetup.js');
        $gameId = $this->_request->getParam('gameId');
        if (!empty($gameId)) {
            $this->_namespace->gameId = $gameId; // zapisuję gemeId do sesji
            if(isset($this->_namespace->armyId)) {
                unset($this->_namespace->armyId);
            }
            $modelGame = new Application_Model_Game($gameId);
            $modelGame->updateGameMaster($this->_namespace->player['playerId']);
            $playersInGame = $modelGame->getPlayersWaitingForGame();
            $this->view->colors = $modelGame->getAllColors();
            if($modelGame->isPlayerInGame($this->_namespace->player['playerId'])){
                $modelGame->disconnectFromGame($gameId, $this->_namespace->player['playerId']);
            }
            $modelGame->joinGame($this->_namespace->player['playerId']);
            $this->view->game = $modelGame->getGame(); // pobieram informację na temat gry
            $this->_namespace->player['ready'] = $modelGame->isPlayerReady($this->_namespace->player['playerId']);
            $this->view->player = $this->_namespace->player;
        } else {
            throw new Exception('Brak gameId!');
        }
    }

    public function startAction() {
        if (!empty($this->_namespace->gameId)) {
            if (empty($this->_namespace->armyId)) {
                $modelGame = new Application_Model_Game($this->_namespace->gameId);
                if(!$modelGame->isPlayerReady($this->_namespace->player['playerId'])){
                    $this->_helper->redirector('index', 'new');
                }
                $modelArmy = new Application_Model_Army($this->_namespace->gameId);
                $modelBoard = new Application_Model_Board();
                $modelHero = new Application_Model_Hero($this->_namespace->player['playerId']);
                $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
                $startPositions = $modelBoard->getDefaultStartPositions();
                $playerHeroes = $modelHero->getHeroes();
                $this->_namespace->player['color'] = $modelGame->getPlayerColor($this->_namespace->player['playerId']);
                if(empty($playerHeroes)) {
                    $modelHero->createHero();
                    $playerHeroes = $modelHero->getHeroes();
                }
                $armyId = $modelArmy->createArmy(
                        $startPositions[$this->_namespace->player['color']]['position'],
                        $this->_namespace->player['playerId']);
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
        } else {
            throw new Exception('Brak gameId!');
        }
    }

}

