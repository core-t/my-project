<?php

class HeroController extends Game_Controller_Action
{

    public function _init()
    {
        /* Initialize action controller here */
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
        $this->view->headScript()->prependFile($this->view->baseUrl() . '/js/jquery.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jWebSocket.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jwsChannelPlugIn.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/index.websocket.js');
        new Application_View_Helper_Logout($this->_namespace->player);
        new Application_View_Helper_Menu();
        new Application_View_Helper_Websocket();
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

