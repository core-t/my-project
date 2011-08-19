<?php

class FacebookController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
        Zend_Session::start();
        $this->_namespace = new Zend_Session_Namespace(); // default namespace
    }

    public function indexAction() {
        // action body
        $signed_request = $this->_getParam('signed_request', null);
        if (empty($signed_request)) {
            throw new Application_Model_FacebookException('Security Error');
        }
        $this->_FB = new Application_Model_Facebook($signed_request);
        // if the user has not installed,
        // redirect to the allow URL
        if (!$this->_FB->hasInstalled) {
            $this->_helper->layout->disableLayout();
            $this->view->redirect_url = Facebook_Model_Facebook::FACEBOOK_ALLOW_URL . '?client_id=' . Facebook_Model_Facebook::FACEBOOK_APP_ID . '&redirect_uri=' . $_SERVER['HTTP_REFERER'];
            $this->_helper->viewRenderer->setScriptAction('redirect');
        } else {
            $this->_namespace->fbId = $this->_FB->fbData['user_id'];
            $modelPlayer = new Application_Model_Player($this->_namespace->fbId);
            if($modelPlayer->noPlayer()) {
                $playerId = $modelPlayer->createPlayer();
                if($playerId) {
                    $modelHero = new Application_Model_Hero($playerId);
                    $modelHero->createHero();
                }
            }
            $fbUserInfo = $this->_FB->getUserInfo();
            $modelPlayer->
            $playerActivity = new Warlords_Player_Activity();
            $player = $modelPlayer->getPlayer();

            if(!$playerActivity->isActive( $player['playerId'])) {
                $this->_helper->redirector('index', 'index');
            } else {
                $this->view->active = true;
            }
//            $this->view->signedRequest = $this->_FB->getSignedRequest();
//            $this->view->fbid = $this->_FB->fbData['user_id'];
//            Zend_Debug::dump($fbUserInfo);
        }
    }

}

