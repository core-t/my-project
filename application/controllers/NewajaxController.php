<?php

class NewajaxController extends Game_Controller_Action
{

    public function _init()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function refreshAction()
    {
        $m = new Application_Model_Game();
        $response = $modelGame->getOpen();
        echo Zend_Json::encode($response);
    }

    public function nopAction()
    {
        $mapId = $this->_request->getParam('mapId');
        $form = new Application_Form_NumberOfPlayers(array('mapId' => $mapId));
        echo Zend_Json::encode($form->__toString());
    }
}