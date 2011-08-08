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

    }

    public function indexAction() {
        // action body
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        if ($modelGame->isActive()) {
            $this->view->headScript()->appendFile('/js/jWebSocket.js');
            $this->view->headScript()->appendFile('/js/jwsChannelPlugIn.js');
            $this->view->headScript()->appendFile('/js/game.js');
            $this->view->headScript()->appendFile('/js/game.libs.js');
            $this->view->headScript()->appendFile('/js/game.zoom.js');
            $this->view->headScript()->appendFile('/js/game.websocket.js');
            $this->view->headScript()->appendFile('/js/game.ajax.js');
            $this->view->headScript()->appendFile('/js/game.message.js');
            $this->view->headScript()->appendFile('/js/game.chanels.js');
            $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
            $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/game.css');
            $this->_helper->layout->setLayout('game');
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
                    $this->view->turn['nr'] = $game['turnNumber'];
                    $this->_namespace->turn = $this->view->turn;
                }
                if($this->_namespace->player['playerId'] == $player['playerId']){
                    $this->view->gold = $player['gold'];
                }
            }
            $this->view->color = $this->_namespace->player['color'];
            $this->view->playerId = $this->_namespace->player['playerId'];
            $this->view->castlesSchema = array();
            $castlesSchema = $modelBoard->getCastlesSchema();
            $razed = $modelCastle->getRazedCastles();
            $this->view->ruins = $modelBoard->getRuins();
            $this->view->fields = $modelBoard->getBoardFields();
            foreach($castlesSchema as $id=>$castle){
                if(!isset($razed[$id])){
                    $this->view->castlesSchema[$id] = $castle;
                    $y = $castle['position']['y']/40;
                    $x = $castle['position']['x']/40;
                    $this->view->fields[$y][$x] = 'r';
                    $this->view->fields[$y + 1][$x + 1] = 'r';
                }
            }
            $this->view->colors = $modelGame->getAllColors();
        } else {
            throw new Exception('Game initialization error');
        }
    }

    public function testAction() {
        $this->view->headScript()->appendFile('/js/jWebSocket.js');
        $this->view->headScript()->appendFile('/js/jwsChannelPlugIn.js');
        $this->_helper->layout->setLayout('game');
//         $castles = array();
//         $this->_helper->layout->disableLayout();
//         $str = Application_Model_Board::production();
//         $model = new Application_Model_Board();
//         $Schema = $model->getCastlesSchema();
//         $arr = explode("\n", $str);
//         unset($arr[0], $arr[1]);
//         foreach($arr as $k => $line) {
//             if(trim(substr($line,0,1))) {
//                 $lineExp = explode('(', $line);
//                 $name = trim($lineExp[0]);
//                 $castles[$name] = array();
//                 $castles[$name]['defense'] = substr($lineExp[1],0,1);
//                 if(!strpos($lineExp[1],'-')) {
//                     $castles[$name]['capital'] = true;
//                 }
//                 $units = substr($line,33);//echo $units.'<br/>';
//                 if(($pos = strpos($units,'Light Infantry')) !== false) {
//                     $data = trim(substr($units,$pos+14));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                     $castles[$name]['production']['Light Infantry'] = array('time' => $data[0], 'cost' => $data[1]);
//                     $pos = false;
//                 }
//                 if(($pos = strpos($units,'Heavy Infantry')) !== false) {
//                     $data = trim(substr($units,$pos+14));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                     $castles[$name]['production']['Heavy Infantry'] = array('time' => $data[0], 'cost' => $data[1]);
//                     $pos = false;
//                 }
//                 if(($pos = strpos($units,'Dwarves')) !== false) {
//                     $data = trim(substr($units,$pos+7));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                     $castles[$name]['production']['Dwarves'] = array('time' => $data[0], 'cost' => $data[1]);
//                     $pos = false;
//                 }
//                 if(($pos = strpos($units,'Giants')) !== false) {
//                     $data = trim(substr($units,$pos+6));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                     $castles[$name]['production']['Giants'] = array('time' => $data[0], 'cost' => $data[1]);
//                     $pos = false;
//                 }
//                 if(($pos = strpos($units,'Archers')) !== false) {
//                     $data = trim(substr($units,$pos+7));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                     $castles[$name]['production']['Archers'] = array('time' => $data[0], 'cost' => $data[1]);
//                     $pos = false;
//                 }
//                 if(($pos = strpos($units,'Navy')) !== false) {
//                     $data = trim(substr($units,$pos+4));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                     $castles[$name]['production']['Navy'] = array('time' => $data[0], 'cost' => $data[1]);
//                     $pos = false;
//                 }
//                 if(($pos = strpos($units,'Wolves')) !== false) {
//                     $data = trim(substr($units,$pos+6));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                     $castles[$name]['production']['Wolves'] = array('time' => $data[0], 'cost' => $data[1]);
//                     $pos = false;
//                 }
//                 if(($pos = strpos($units,'Cavalry')) !== false) {
//                     $data = trim(substr($units,$pos+7));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                     $castles[$name]['production']['Cavalry'] = array('time' => $data[0], 'cost' => $data[1]);
//                     $pos = false;
//                 }
//                 if($pos = strpos($units,'Griffins') !== false) {
//                     $data = trim(substr($units,$pos+8));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                     $castles[$name]['production']['Griffins'] = array('time' => $data[0], 'cost' => $data[1]);
//                     $pos = false;
//                 }
//                 if(!trim(substr($arr[$k+1],0,1))) {
//                     $units2 = $arr[$k+1];
//                     if(($pos = strpos($units2,'Light Infantry')) !== false) {
//                         $data = trim(substr($units2,$pos+14));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                         $castles[$name]['production']['Light Infantry'] = array('time' => $data[0], 'cost' => $data[1]);
//                         $pos = false;
//                     }
//                     if(($pos = strpos($units2,'Heavy Infantry')) !== false) {
//                         $data = trim(substr($units2,$pos+14));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                         $castles[$name]['production']['Heavy Infantry'] = array('time' => $data[0], 'cost' => $data[1]);
//                         $pos = false;
//                     }
//                     if(($pos = strpos($units2,'Dwarves')) !== false) {
//                         $data = trim(substr($units2,$pos+7));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                         $castles[$name]['production']['Dwarves'] = array('time' => $data[0], 'cost' => $data[1]);
//                         $pos = false;
//                     }
//                     if(($pos = strpos($units2,'Giants')) !== false) {
//                         $data = trim(substr($units2,$pos+6));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                         $castles[$name]['production']['Giants'] = array('time' => $data[0], 'cost' => $data[1]);
//                         $pos = false;
//                     }
//                     if(($pos = strpos($units2,'Archers')) !== false) {
//                         $data = trim(substr($units2,$pos+7));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                         $castles[$name]['production']['Archers'] = array('time' => $data[0], 'cost' => $data[1]);
//                         $pos = false;
//                     }
//                     if(($pos = strpos($units2,'Wolves')) !== false) {
//                         $data = trim(substr($units2,$pos+6));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                         $castles[$name]['production']['Wolves'] = array('time' => $data[0], 'cost' => $data[1]);
//                         $pos = false;
//                     }
//                     if(($pos = strpos($units2,'Cavalry')) !== false) {
//                         $data = trim(substr($units2,$pos+7));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                         $castles[$name]['production']['Cavalry'] = array('time' => $data[0], 'cost' => $data[1]);
//                         $pos = false;
//                     }
//                     if(($pos = strpos($units2,'Griffins')) !== false) {
//                         $data = trim(substr($units2,$pos+8));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                         $castles[$name]['production']['Griffins'] = array('time' => $data[0], 'cost' => $data[1]);
//                         $pos = false;
//                     }
//                     if(($pos = strpos($units2,'Navy')) !== false) {
//                         $data = trim(substr($units2,$pos+4));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                         $castles[$name]['production']['Navy'] = array('time' => $data[0], 'cost' => $data[1]);
//                         $pos = false;
//                     }
//                     if(($pos = strpos($units2,'Pegasi')) !== false) {
//                         $data = trim(substr($units2,$pos+6));
//                     $data = substr($data,1,strpos($data,')')-1);
//                     $data = explode('/', $data);
//                         $castles[$name]['production']['Pegasi'] = array('time' => $data[0], 'cost' => $data[1]);
//                         $pos = false;
//                     }
//                 }
//             }
//         }
//         foreach($Schema as $k => $castle) {
//             if($castles[$castle['name']]) {
//                 echo '$this->_castles['.$k.'] = array(<br/>';
//                 echo '\'name\' => \''.$castle['name'].'\',<br/>';
//                 echo '\'income\' => '.$castle['income'].',<br/>';
//                 echo '\'defensePoints\' => '.$castles[$castle['name']]['defense'].',<br/>';
//                 echo '\'position\' => array(\'x\' => '.$castle['position']['x'].', \'y\' => '.$castle['position']['y'].'),<br/>';
//                 if(isset($castles[$castle['name']]['capital']))
//                     echo '\'capital\' => true,<br/>';
//                 else
//                     echo '\'capital\' => false,<br/>';
//                 echo '\'production\' => array(<br/>';
//
//                     foreach($castles[$castle['name']]['production'] as $k=>$p) {
//                         echo '\''.$k.'\' => array(\'time\' => \''.$p['time'].'\', \'cost\' => \''.$p['cost'].'\'),<br/>';
//                     }
//                     echo ')<br/>';
//                 echo ');<br/>';
//             }
//         }
    }

}

