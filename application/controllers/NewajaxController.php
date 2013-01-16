<?php

class NewajaxController extends Game_Controller_Action {

    public function _init() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function refreshAction() {
        // action body
        $modelGame = new Application_Model_Game();
        $response = $modelGame->getOpen();
        echo Zend_Json::encode($response);
    }

}

