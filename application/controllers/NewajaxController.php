<?php

class NewajaxController extends Game_Controller_Action
{

    public function _init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
    }

    public function refreshAction() {
        // action body
        $modelGame = new Application_Model_Game();
        $response = $modelGame->getOpen();
        $this->view->response = Zend_Json::encode($response);
    }


}

