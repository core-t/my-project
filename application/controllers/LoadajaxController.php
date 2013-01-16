<?php

class LoadajaxController extends Game_Controller_Ajax {

    public function refreshAction() {
        // action body
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        $modelGame->updatePlayerInGame($this->_namespace->player['playerId']);
        $response = $modelGame->getPlayersInGameLoad();
        echo Zend_Json::encode($response);
    }

    public function updateAction() {
        // action body
        $color = $this->_request->getParam('color');
        if (!empty($color)) {
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $playerId = $modelGame->getPlayerIdByColor($color);
            if (!empty($playerId)) {
                if ($modelGame->playerIsAlive($playerId)) {
                    $modelGame->updatePlayerInGame($playerId);
                    $response = $modelGame->getPlayersWaitingForGame();
                    echo Zend_Json::encode($response);
                } else {
                    throw new Exception('Player not alive!');
                }
            } else {
                throw new Exception('Brak playerId!');
            }
        } else {
            throw new Exception('Brak color!');
        }
    }

}

