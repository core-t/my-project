<?php

class HalloffameController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $mPlayer = new Application_Model_Player();
        $this->view->hallOfFame = $mPlayer->hallOfFame($this->_request->getParam('page'));
    }

}

