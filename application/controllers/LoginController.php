<?php

class LoginController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->_namespace = Game_Namespace::getNamespace(); // default namespace
        $this->_helper->layout->setLayout('login');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/login.css');
    }

    public function indexAction()
    {
        // action body
        $this->view->form = new Application_Form_Auth();
        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                $modelPlayer = new Application_Model_Player(null, false);
                $playerId = $modelPlayer->auth($this->_request->getParam('login'), $this->_request->getParam('password'));
                if ($playerId) {
                    $this->_namespace->player = $modelPlayer->getPlayer($playerId);
                    $this->_redirect('/index');
                } else {
                    $this->view->form->setDescription($this->view->translate('Incorrect login or password!'));
                }
            }
        }
    }

    public function logoutAction()
    {
        // action body
        Zend_Session::destroy(true);
        $this->_redirect('/login');
    }

    public function registrationAction()
    {
        // action body
        $form = new Application_Form_Registration();
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $modelPlayer = new Application_Model_Player(null, false);
                $data = array(
                    'firstName' => $this->_request->getParam('firstName'),
                    'lastName' => $this->_request->getParam('lastName'),
                    'login' => $this->_request->getParam('login'),
                    'password' => md5($this->_request->getParam('password'))
                );
                $playerId = $modelPlayer->createPlayer($data);
                if ($playerId) {
                    $modelHero = new Application_Model_Hero($playerId);
                    $modelHero->createHero();
                    $this->_namespace->player = $modelPlayer->getPlayer($playerId);
                    $this->_redirect('/index');
                }
            }
        }
        $this->view->form = $form;
    }

}

