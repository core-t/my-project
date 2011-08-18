<?php

/*
 * zapisać do bazy zamki, w których gracze rozpoczynają grę oraz ich armie a w nich herosi
 *
 */

class GamesetupController extends Warlords_Controller_Action {

    public function _init() {
        /* Initialize action controller here */
//        $this->view->headScript()->prependFile('https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js');
        $this->view->headScript()->prependFile($this->view->baseUrl() . '/js/jquery.min.js');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/gamesetup.css');
    }

    public function indexAction() {
        // action bodys
        $modelGame = new Application_Model_Game();
        $this->view->openGames = $modelGame->getOpen();
        if ($this->_namespace->gameId) {
            $modelGame->disconnectFromGame($this->_namespace->gameId, $this->_namespace->player['playerId']);
            unset($this->_namespace->gameId);
        }
        $this->view->player = $this->_namespace->player;
    }

    public function createAction() {
        $this->view->form = new Application_Form_Creategame ();
        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                $modelGame = new Application_Model_Game ();
                $data = array(
                    'numberOfPlayers' => $this->_request->getParam('numberOfPlayers'),
                    'gameMasterId' => $this->_namespace->player['playerId'],
                    'turnPlayerId' => $this->_namespace->player['playerId']
                );
                $gameId = $modelGame->createGame($data);
                $this->_helper->redirector('setup', 'gamesetup', null, array('gameId' => $gameId));
            }
        }
    }

    public function setupAction() {
        $this->view->headScript()->appendFile('/js/gamesetup.js');
        $gameId = $this->_request->getParam('gameId');
        if (!empty($gameId)) {
            $this->_namespace->gameId = $gameId; // zapisuję gemeId do sesji
            if(isset($this->_namespace->armyId)) {
                unset($this->_namespace->armyId);
            }
            $modelGame = new Application_Model_Game($gameId);
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

    private function findPlayerColor($playersInGame, $playerColors) {
        foreach ($playersInGame as $player) {
            foreach ($playerColors as $k => $color) {
                if ($player['color'] == $color) {
                    unset($playerColors[$k]);
                }
            }
        }
        foreach ($playerColors as $color) {
            return $color;
        }
    }

    public function startAction() {
        if (!empty($this->_namespace->gameId)) {
            if (empty($this->_namespace->armyId)) {
                $modelGame = new Application_Model_Game($this->_namespace->gameId);
                $modelArmy = new Application_Model_Army($this->_namespace->gameId);
                $modelBoard = new Application_Model_Board();
                $modelHero = new Application_Model_Hero($this->_namespace->player['playerId']);
                $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
                $startPositions = $modelBoard->getDefaultStartPositions();
                $playerHeroes = $modelHero->getHeroes();
                $this->_namespace->player['color'] = $modelGame->getPlayerColor($this->_namespace->player['playerId']);
//                 throw new Exception($playerColor);
                if(empty($playerHeroes)) {
                    $modelHero->createHero();
                    $playerHeroes = $modelHero->getHeroes();
                }
                $armyId = $modelArmy->createArmy(
                        $startPositions[$this->_namespace->player['color']]['position'],
                        $this->_namespace->player['playerId']);
                $res = $modelArmy->addHeroToArmy($armyId, $playerHeroes[0]['heroId']);
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

