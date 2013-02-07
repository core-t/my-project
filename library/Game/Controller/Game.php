<?php

abstract class Game_Controller_Game extends Game_Controller_Action {

    public function init() {
        parent::init();

        if (empty($this->_namespace->player['playerId'])) {
            $this->_redirect('/login');
        }

        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
        $this->view->headScript()->prependFile($this->view->baseUrl() . '/js/jquery.js');

        $this->view->Logout($this->_namespace->player);
        $this->view->Websocket();
        $this->view->googleAnalytics();

        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

}
