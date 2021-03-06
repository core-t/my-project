<?php

class NewController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $this->view->form = new Application_Form_Creategame ();
        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                $modelGame = new Application_Model_Game ();
                $gameId = $modelGame->createGame($this->_request->getParams(), $this->_namespace->player['playerId']);
                if ($gameId) {
                    $mMapPlayers = new Application_Model_MapPlayers($this->_request->getParam('mapId'));
                    $colors = $mMapPlayers->getAll();
                    $mPlayersInGame = new Application_Model_PlayersInGame($gameId);
                    $mPlayersInGame->joinGame($this->_namespace->player['playerId']);
                    $mPlayersInGame->updatePlayerReady($this->_namespace->player['playerId'], $colors[0]['mapPlayerId']);
                    $mPlayersInGame = new Application_Model_PlayersInGame($gameId);
                    for ($i = 1; $i < $this->_request->getParam('numberOfPlayers'); $i++) {
                        $playerId = $mPlayersInGame->getComputerPlayerId();
                        if (!$playerId) {
                            $modelPlayer = new Application_Model_Player(null, false);
                            $playerId = $modelPlayer->createComputerPlayer();
                            $modelHero = new Application_Model_Hero($playerId);
                            $modelHero->createHero();
                        }
                        $mPlayersInGame->joinGame($playerId);
                        $mPlayersInGame->updatePlayerReady($playerId, $colors[$i]['mapPlayerId']);
                    }
                    $this->_redirect('/' . Zend_Registry::get('lang') . '/setup/index/gameId/' . $gameId);
                }
            }
        }

        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/playerslist.css?v=' . Zend_Registry::get('config')->version);
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/new.css?v=' . Zend_Registry::get('config')->version);

        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/new.js?v=' . Zend_Registry::get('config')->version);

        if ($this->_namespace->gameId) {
            unset($this->_namespace->gameId);
        }

        $this->view->player = $this->_namespace->player;
    }

}

