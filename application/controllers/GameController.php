<?php

class GameController extends Game_Controller_Game {

    public function indexAction() {
        // action body
        $mGame = new Application_Model_Game($this->_namespace->gameId);
//        if (!$mGame->isActive()) {
//            throw new Exception('Game initialization error');
//        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/game.css');
        $this->view->headScript()->appendFile('/js/game/game.js');
        $this->view->headScript()->appendFile('/js/game/castles.js');
        $this->view->headScript()->appendFile('/js/game/armies.js');
        $this->view->headScript()->appendFile('/js/game/astar.js');
        $this->view->headScript()->appendFile('/js/game/towers.js');
        $this->view->headScript()->appendFile('/js/game/ruins.js');
        $this->view->headScript()->appendFile('/js/game/test.js');
        $this->view->headScript()->appendFile('/js/game/game.libs.js');
        $this->view->headScript()->appendFile('/js/game/game.zoom.js');
        $this->view->headScript()->appendFile('/js/game/game.websocket.js');
        $this->view->headScript()->appendFile('/js/game/game.ajax.js');
        $this->view->headScript()->appendFile('/js/game/game.message.js');
        $this->_helper->layout->setLayout('game');
        $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
        $modelArmy = new Application_Model_Army($this->_namespace->gameId);
        $modelRuin = new Application_Model_Ruin($this->_namespace->gameId);
        $modelTower = new Application_Model_Tower($this->_namespace->gameId);
        $modelUnit = new Application_Model_Unit();
        $modelArtefact = new Application_Model_Artefact();
        $this->view->artefacts = $modelArtefact->getArtefacts();
        $this->view->units = $modelUnit->getUnits();
        $neutralTowers = Application_Model_Board::getTowers();
        $playersTowers = $modelTower->getTowers();
        $towers = array();
        foreach (array_keys($neutralTowers) as $k)
        {
            $towers[$k] = $neutralTowers[$k];
            if (isset($playersTowers[$k])) {
                $towers[$k]['color'] = $playersTowers[$k];
            } else {
                $towers[$k]['color'] = 'neutral';
            }
        }
        $this->view->towers = $towers;
        $players = $mGame->getPlayersInGameReady();
        $this->view->players = array();
        $this->view->turn = array();

        $game = $mGame->getGame();

        $this->view->game = $game;
        foreach ($players as $player)
        {
            $this->view->players[$player['color']]['armies'] = $modelArmy->getPlayerArmies($player['playerId']);
            $this->view->players[$player['color']]['castles'] = $modelCastle->getPlayerCastles($player['playerId']);
            $this->view->players[$player['color']]['turnActive'] = $player['turnActive'];
            $this->view->players[$player['color']]['computer'] = $player['computer'];
            $this->view->players[$player['color']]['lost'] = $player['lost'];
            if ($game['turnPlayerId'] == $player['playerId']) {
                $this->view->turn['playerId'] = $player['playerId'];
                $this->view->turn['color'] = $player['color'];
                $this->view->turn['nr'] = $game['turnNumber'];
                $this->_namespace->turn = $this->view->turn;
            }
            if ($this->_namespace->player['playerId'] == $player['playerId']) {
                $this->view->gold = $player['gold'];
                $this->view->accessKey = $player['accessKey'];
            }
        }
        $this->view->color = $this->_namespace->player['color'];
        $this->view->id = $this->_namespace->player['playerId'];
        if ($this->view->turn['playerId'] == $this->_namespace->player['playerId']) {
            $this->view->myTurn = 'true';
        } else {
            $this->view->myTurn = 'false';
        }

        $gameMasterId = $mGame->getGameMaster();
        if ($gameMasterId == $this->_namespace->player['playerId']) {
            $this->view->myGame = 1;
        } else {
            $this->view->myGame = 0;
        }

        $this->view->castlesSchema = array();
        $castlesSchema = Application_Model_Board::getCastlesSchema();
        $razed = $modelCastle->getRazedCastles();
        $this->view->ruins = Application_Model_Board::getRuins();
        $emptyRuins = $modelRuin->getVisited();
        foreach ($emptyRuins as $id => $ruin)
        {
            $this->view->ruins[$id]['e'] = 1;
        }
        $this->view->fields = Application_Model_Board::getBoardFields();
        foreach ($castlesSchema as $id => $castle)
        {
            if (!isset($razed[$id])) {
                $this->view->castlesSchema[$id] = $castle;
            }
        }
    }

    public function boardAction() {
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/board.css');
        $this->view->headScript()->prependFile('/js/jquery.min.js');

        $this->view->headScript()->appendFile('/js/game/game.zoom.js');

        $this->_helper->layout->setLayout('board');
    }

    public function testAction() {
//         $this->view->headScript()->appendFile('/js/jWebSocket.js');
//         $this->view->headScript()->appendFile('/js/jwsChannelPlugIn.js');
//         $this->_helper->layout->setLayout('game');
//         $castles = array();
        $this->_helper->layout->disableLayout();
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

