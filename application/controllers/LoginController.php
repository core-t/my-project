<?php

class LoginController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        Zend_Session::start();
        $this->_namespace = new Zend_Session_Namespace(); // default namespace
    }

    public function indexAction()
    {
        // action body
        $form = new Application_Form_Fbid();
        if ($this->_request->isPost()) {
            if ($form->isValid($this->_request->getPost())) {
                $this->_namespace->fbId = $this->_request->getParam('fbid');
                $modelPlayer = new Application_Model_Player($this->_namespace->fbId);
                if($modelPlayer->noPlayer()) {
                    $playerId = $modelPlayer->createPlayer();
                    if($playerId) {
                        $modelHero = new Application_Model_Hero($playerId);
                        $modelHero->createHero();
                    }
                }
//                $playerActivity = new Warlords_Player_Activity();
//                $player = $modelPlayer->getPlayer();
//
//                if(!$playerActivity->isActive( $player['playerId'])) {
                    $this->_helper->redirector('index', 'index');
//                } else {
//                    $this->view->active = true;
//                }

            }
        }
        $this->view->form = $form;
    }

    public function logoutAction()
    {
        // action body
        Zend_Session::destroy(true);
        $this->_helper->redirector('index', 'login');
    }


}



