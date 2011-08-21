<?php

class LoadajaxController extends Game_Controller_Action
{

    public function _init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak gameId!');
        }
    }

    public function refreshAction()
    {
        // action body
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        $modelGame->updatePlayerInGame($this->_namespace->player['playerId']);
        $response = $modelGame->getPlayersWaitingForGame();
        $this->view->response = Zend_Json::encode($response);
    }

    public function updateAction()
    {
        // action body
        $color = $this->_request->getParam('color');
        if(!empty($color)){
            $modelGame = new Application_Model_Game($this->_namespace->gameId);
            $playerId = $modelGame->getPlayerIdByColor($color);
            if(!empty($playerId)){
                if($modelGame->playerIsAlive($playerId)){
                    $modelGame->updatePlayerInGame($playerId);
                    $response = $modelGame->getPlayersWaitingForGame();
                    $this->view->response = Zend_Json::encode($response);
                }else{
                    throw new Exception('Player not alive!');
                }
            }else{
                throw new Exception('Brak playerId!');
            }
        }else{
            throw new Exception('Brak color!');
        }
    }
}



