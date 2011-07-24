<?php

class ProductionController extends Warlords_Controller_Action
{

    public function _init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function setAction()
    {
        // action body
        $castleId = $this->_request->getParam('castleId');
        if ($castleId != null) {

        } else {
            throw new Exception('Brak "castleId"!');
        }
    }

    public function stopAction()
    {
        // action body
    }


}





