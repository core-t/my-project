<?php

class HeroController extends Warlords_Controller_Action
{

    public function _init()
    {
        /* Initialize action controller here */
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
    }

    public function indexAction()
    {
        // action body
        $modelHero = new Application_Model_Hero ($this->_namespace->player['playerId']);
        $this->view->heroes = $modelHero->getHeroes();

        $this->view->form = new Application_Form_Hero ();
        $this->view->form->setDefault('name', $this->view->heroes[0]['name']);
        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {

                $modelHero->changeHoroName($this->view->heroes[0]['heroId'], $this->_request->getParam('name'));
                $this->_helper->redirector('index', 'hero');
            }
        }
    }


}

