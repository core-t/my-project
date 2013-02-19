<?php

class TowerController extends Game_Controller_Ajax {

    public function addAction() {
        $towerId = $this->_request->getParam('tid');
        $color = $this->_request->getParam('c');
        if ($towerId !== null || empty($color)) {
            $modelTower = new Application_Model_Tower($this->_namespace->gameId);
            $mGame = new Application_Model_Game($this->_namespace->gameId);
            $playerId = $mGame->getPlayerIdByColor($color);
            if ($modelTower->towerExists($towerId)) {
                $modelTower->changeTowerOwner($towerId, $playerId);
            } else {
                $modelTower->addTower($towerId, $playerId);
            }
        } else {
            throw new Exception('Brak "towerId"!');
        }
    }

}
