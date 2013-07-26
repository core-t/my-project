<?php

class Admin_MapController extends Coret_Controller_Backend
{

    public function init()
    {
        $this->view->title = 'Map';
        parent::init();
    }

    public function aAction()
    {
        $mapId = $this->_request->getParam('mapId');
        
    }
}

