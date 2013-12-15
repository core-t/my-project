<?php

class LoginController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_namespace = Game_Namespace::getNamespace();
        $this->_helper->layout->setLayout('login');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/login.css');

        $this->view->jquery();
        $this->view->headScript()->appendFile('/js/login.js?v=' . Zend_Registry::get('config')->version);
    }

    public function indexAction()
    {
        $this->view->form = new Application_Form_Auth();
        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                $modelPlayer = new Application_Model_Player(null, false);
                $playerId = $modelPlayer->auth($this->_request->getParam('login'), $this->_request->getParam('password'));
                if ($playerId) {
                    $this->_namespace->player = $modelPlayer->getPlayer($playerId);
                    $this->_redirect($this->view->url(array('controller' => 'index')));
                } else {
                    $this->view->form->setDescription($this->view->translate('Incorrect login or password!'));
                }
            }
        }
    }

    public function logoutAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        Zend_Session::destroy(true);
        $this->_redirect($this->view->url(array('controller' => 'login', 'action' => null)));
    }

    public function registrationAction()
    {
        $form = new Application_Form_Registration();
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $mPlayer = new Application_Model_Player();
                $data = array(
                    'firstName' => $this->_request->getParam('firstName'),
                    'lastName' => $this->_request->getParam('lastName'),
                    'login' => $this->_request->getParam('login'),
                    'password' => md5($this->_request->getParam('password'))
                );
                $playerId = $mPlayer->createPlayer($data);
                if ($playerId) {
                    $modelHero = new Application_Model_Hero($playerId);
                    $modelHero->createHero();
                    $this->_namespace->player = $mPlayer->getPlayer($playerId);
                    $this->_redirect($this->view->url(array('controller' => 'index', 'action' => null)));
                }
            }
        }
        $this->view->form = $form;
    }

}

