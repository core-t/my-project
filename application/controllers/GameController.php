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
        $castles = array();
        $this->_helper->layout->disableLayout();
        $str = Application_Model_Board::production();
        $model = new Application_Model_Board();
        $Schema = $model->getCastlesSchema();
        $arr = explode("\n", $str);
        unset($arr[0], $arr[1]);
        foreach($arr as $k => $line) {
            if(trim(substr($line,0,1))) {
                $lineExp = explode('(', $line);
                $name = trim($lineExp[0]);
                $castles[$name] = array();
                $castles[$name]['defense'] = substr($lineExp[1],0,1);
                if(!strpos($lineExp[1],'-')) {
                    $castles[$name]['capital'] = true;
                }
                $units = substr($line,33);//echo $units.'<br/>';
                if(strpos($units,'Lt Inf') !== false) {
                    $castles[$name]['production'][] = 'Lt Inf';
                }
                if(strpos($units,'Hvy Inf') !== false) {
                    $castles[$name]['production'][] = 'Hvy Inf';
                }
                if(strpos($units,'Dwarves') !== false) {
                    $castles[$name]['production'][] = 'Dwarves';
                }
                if(strpos($units,'Giants') !== false) {
                    $castles[$name]['production'][] = 'Giants';
                }
                if(strpos($units,'Archers') !== false) {
                    $castles[$name]['production'][] = 'Archers';
                }
                if(strpos($units,'Navy') !== false) {
                    $castles[$name]['production'][] = 'Navy';
                }
                if(strpos($units,'Wolves') !== false) {
                    $castles[$name]['production'][] = 'Wolves';
                }
                if(strpos($units,'Cavalry') !== false) {
                    $castles[$name]['production'][] = 'Cavalry';
                }
                if(strpos($units,'Griffins') !== false) {
                    $castles[$name]['production'][] = 'Griffins';
                }
                if(!trim(substr($arr[$k+1],0,1))) {
                    $units2 = $arr[$k+1];
                    if(strpos($units2,'Lt Inf') !== false) {
                        $castles[$name]['production'][] = 'Lt Inf';
                    }
                    if(strpos($units2,'Hvy Inf') !== false) {
                        $castles[$name]['production'][] = 'Hvy Inf';
                    }
                    if(strpos($units2,'Dwarves') !== false) {
                        $castles[$name]['production'][] = 'Dwarves';
                    }
                    if(strpos($units2,'Giants') !== false) {
                        $castles[$name]['production'][] = 'Giants';
                    }
                    if(strpos($units2,'Archers') !== false) {
                        $castles[$name]['production'][] = 'Archers';
                    }
                    if(strpos($units2,'Wolves') !== false) {
                        $castles[$name]['production'][] = 'Wolves';
                    }
                    if(strpos($units2,'Cavalry') !== false) {
                        $castles[$name]['production'][] = 'Cavalry';
                    }
                    if(strpos($units2,'Griffins') !== false) {
                        $castles[$name]['production'][] = 'Griffins';
                    }
                    if(strpos($units2,'Navy') !== false) {
                        $castles[$name]['production'][] = 'Navy';
                    }
                    if(strpos($units2,'Pegasi') !== false) {
                        $castles[$name]['production'][] = 'Pegasi';
                    }
//                     echo $arr[$k+1].'<br/>';
                }
            }
        }
        foreach($Schema as $k => $castle) {
            if($castles[$castle['name']]) {
//                 echo $k.'<pre>';print_r($castles[$castle['name']]);echo '</pre>';
                echo '$this->_castles['.$k.'] = array(<br/>';
                echo '\'name\' => \''.$castle['name'].'\',<br/>';
                echo '\'income\' => '.$castle['income'].',<br/>';
                echo '\'defensePoints\' => '.$castles[$castle['name']]['defense'].',<br/>';
                echo '\'position\' => array(\'x\' => '.$castle['position']['x'].', \'y\' => '.$castle['position']['y'].'),<br/>';
                if(isset($castles[$castle['name']]['capital']))
                    echo '\'capital\' => true,<br/>';
                else
                    echo '\'capital\' => false,<br/>';
                echo '\'production\' => array(<br/>';

                    foreach($castles[$castle['name']]['production'] as $p) {
                        echo '\''.$p.'\',<br/>';
                    }
                    echo ')<br/>';
                echo ');<br/>';
            }
        }
//         var_dump($castles);
//         echo '<pre>';print_r($castles);echo '</pre>';
    }

}

