<?php

abstract class Game_Controller_Ajax extends Game_Controller_Action {

    public final function init() {
        parent::init();

        if (empty($this->_namespace->player['playerId'])) {
            throw new Exception('No "playerId"!');
        }

        if (empty($this->_namespace->gameId)) {
            throw new Exception('No "gameId"!');
        }

        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

}
