<?php

class FacebookController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
        $this->_namespace = Game_Namespace::getNamespace(); // default namespace
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
            $this->view->redirect_url = Application_Model_Facebook::FACEBOOK_ALLOW_URL . '?client_id=' . Application_Model_Facebook::FACEBOOK_APP_ID . '&redirect_uri=' . $_SERVER['HTTP_REFERER'];
            $this->_helper->viewRenderer->setScriptAction('redirect');
        } else {
            $modelPlayer = new Application_Model_Player($this->_FB->fbData['user_id']);
            if ($modelPlayer->noPlayer()) {
                $data = array(
                    'fbId' => $this->_FB->fbData['user_id'],
                    'activity' => '2011-06-15'
                );
                $playerId = $modelPlayer->createPlayer($data);
                if ($playerId) {
                    $modelHero = new Application_Model_Hero($playerId);
                    $modelHero->createHero();
                }
            }
            $fbUserInfo = $this->_FB->getUserInfo();
            $data = array(
                'firstName' => $fbUserInfo['first_name'],
                'lastName' => $fbUserInfo['last_name'],
                'locale' => $fbUserInfo['locale']
            );
            $modelPlayer->updatePlayer($data);
//            $playerActivity = new Warlords_Player_Activity();

            $this->_namespace->player = $modelPlayer->getPlayer();

//            if(!$playerActivity->isActive( $player['playerId'])) {
            $this->_helper->redirector('index', 'index');
//            } else {
//                $this->view->active = true;
//            }
//            $this->view->signedRequest = $this->_FB->getSignedRequest();
//            $this->view->fbid = $this->_FB->fbData['user_id'];
//            Zend_Debug::dump($fbUserInfo);
        }
    }

}

