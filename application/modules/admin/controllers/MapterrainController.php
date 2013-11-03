<?php

class Admin_MapterrainController extends Coret_Controller_Backend
{

    public function init()
    {
        $this->view->title = 'Map terrain';
        parent::init();
    }

    public function aAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $m = new Admin_Model_Mapterrain(array(), 1);
        $m->copy();
    }
}

