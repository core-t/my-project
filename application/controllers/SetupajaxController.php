<?php

class SetupajaxController extends Game_Controller_Action {

    public function _init() {
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function allarmiesreadyAction() {
        $modelArmy = new Application_Model_Army($this->_namespace->gameId);
        if ($modelArmy->allArmiesReady()) {
            $result = array('all' => true);
        } else {
            $result = array('all' => false);
        }
        $this->view->response = Zend_Json::encode($result);
    }

}

