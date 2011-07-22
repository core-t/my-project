<?php

class GameController extends Warlords_Controller_Action {

    public function _init() {
        /* Initialize action controller here */
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
//        $this->view->headScript()->prependFile('https://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js');
        $this->view->headScript()->prependFile('/js/jquery.min.js');
//        $this->view->headScript()->appendFile('http://jquery-json.googlecode.com/files/jquery.json-2.2.min.js');
//         $this->view->headScript()->appendFile('/js/jquery.json-2.2.min.js');
//        $this->view->headScript()->appendFile('http://jquery-websocket.googlecode.com/files/jquery.websocket-0.0.1.js');
//         $this->view->headScript()->appendFile('/js/jquery.websocket-0.0.1.js');
//        $this->view->headScript()->appendFile('https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/jquery-ui.min.js');
        $this->view->headScript()->appendFile('/js/game.js');
        $this->view->headScript()->appendFile('/js/game.libs.js');
        $this->view->headScript()->appendFile('/js/game.zoom.js');
        $this->view->headScript()->appendFile('/js/game.websocket.js');
        $this->view->headScript()->appendFile('/js/game.ajax.js');

        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/game.css');

        $this->_helper->layout->setLayout('game');
    }

    public function indexAction() {
        // action body
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        if ($modelGame->isActive()) {
            $modelBoard = new Application_Model_Board();
            $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $startPositions = $modelBoard->getDefaultStartPositions();
            $players = $modelGame->getPlayersInGame();
            $this->view->players = array();
            $this->view->turn = array();
            $game = $modelGame->getGame();
            foreach ($players as $player) {
                $this->view->players[$player['color']]['armies'] = $modelArmy->getPlayerArmies($player['playerId']);
                $this->view->players[$player['color']]['castles'] = $modelCastle->getPlayerCastles($player['playerId']);
                if ($game['turnPlayerId'] == $player['playerId']) {
                    $this->view->turn['playerId'] = $player['playerId'];
                    $this->view->turn['color'] = $player['color'];
                    $this->_namespace->turn = $this->view->turn;
                }
            }
            $this->view->color = $this->_namespace->player['color'];
            $this->view->playerId = $this->_namespace->player['playerId'];
            $this->view->castlesSchema = $modelBoard->getCastlesSchema();
            $this->view->fields = $modelBoard->getBoardFields();
            foreach($this->view->castlesSchema as $castle) {
                $y = $castle['position']['y']/40;
                $x = $castle['position']['x']/40;
                $this->view->fields[$y][$x] = 'r';
                $this->view->fields[$y + 1][$x + 1] = 'r';
            }
            $this->view->colors = $modelGame->getAllColors();
        } else {
            throw new Exception('Game initialization error');
        }
    }

    public function testAction() {
        $this->_helper->layout->disableLayout();
    }

}

