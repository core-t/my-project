<?php

class NewController extends Game_Controller_Game
{

    public function _init()
    {
        /* Initialize action controller here */
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
    }

    public function indexAction()
    {
        // action body
        $this->view->headScript()->prependFile($this->view->baseUrl() . '/js/jquery.min.js');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/playerslist.css');
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
                $gameId = $modelGame->createGame($this->_request->getParam('numberOfPlayers'), $this->_namespace->player['playerId']);
                if($gameId){
                    $this->_helper->redirector('index', 'gamesetup', null, array('gameId' => $gameId));
                }
            }
        }
    }
}

