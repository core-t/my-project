<?php

class HelpController extends Game_Controller_Gui
{

    public function indexAction()
    {
        $mUnit = new Application_Model_Unit();
        $this->view->list = $mUnit->getAll();
    }

}

