<?php

class WebsocketController extends Game_Controller_Ajax {

    public function openAction() {
        $wssuid = $this->_request->getParam('wssuid');
        if ($wssuid) {
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $modelGame->updatePlayerInGameWSSUId($this->_namespace->player['playerId'], $wssuid);
        }
    }

}

