<?php

class HelpController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $mMapUnits = new Application_Model_MapUnits(1);
        $this->view->list = $mMapUnits->getUnits();
    }

}

