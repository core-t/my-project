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
                    'name' => $this->_request->getParam('name'),
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
//            Zend_Debug::dump($playersInGame);
            $playerColors = $modelGame->getAllColors();
//            Zend_Debug::dump($playerColors);
            $color = $modelGame->getPlayerColor($this->_namespace->player['playerId']);
            if (!$color) {
                $color = $this->findPlayerColor($playersInGame, $playerColors);
                $modelGame->joinGame($this->_namespace->player['playerId'], $color);
                $this->_namespace->player['color'] = $color;
            } else {
                $this->_namespace->player['color'] = $color;
            }
            $modelGame->updatePlayerInGame($this->_namespace->player['playerId']);
            $this->view->game = $modelGame->getGame(); // pobieram informację na temat gry
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
                $modelArmy = new Application_Model_Army($this->_namespace->gameId);
                $modelBoard = new Application_Model_Board();
                $modelHero = new Application_Model_Hero($this->_namespace->player['playerId']);
                $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
                $startPositions = $modelBoard->getDefaultStartPositions();
                $playerHeroes = $modelHero->getHeroes();
                if(empty($playerHeroes)) {
                    $modelHero->createHero();
                    $playerHeroes = $modelHero->getHeroes();
                }
                $this->_namespace->armyId = $modelArmy->createArmy(
                        $startPositions[$this->_namespace->player['color']]['position'],
                        $playerHeroes[0]['numberOfMoves'],
                        $this->_namespace->player['playerId']);
                $modelArmy->addHeroToArmy($this->_namespace->armyId, $playerHeroes[0]['heroId']);
                $modelCastle->addCastle($startPositions[$this->_namespace->player['color']]['id'], $this->_namespace->player['playerId']);
            }
        } else {
            throw new Exception('Brak gameId!');
        }
    }

}

