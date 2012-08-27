<?php

class ChatController extends Game_Controller_Action {

    public function _init() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function sendAction() {
        $color = $this->_request->getParam('c');
        $msg = strip_tags($this->_request->getParam('m'));
        if (!empty($color) && !empty($msg)) {
            $mGame = new Application_Model_Game($this->_namespace->gameId);
            $playerId = $mGame->getPlayerIdByColor($color);

            $mChat = new Application_Model_Chat();
            if ($mChat->addChat($playerId, $this->_namespace->gameId, $msg)) {

                $mWebSocket = new Application_Model_WebSocket();
                $mWebSocket->authorizeChannel($mGame->getKeys());
                $mWebSocket->publishChannel($this->_namespace->gameId, $color . '.C.' . $msg);
                $mWebSocket->close();
            }
        } else {
            throw new Exception('Brak "$color" lub "$msg"!');
        }
    }

}
