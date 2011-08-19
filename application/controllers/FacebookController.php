<?php

require_once APPLICATION_PATH . '/../library/Facebook/FacebookException.php';
require_once APPLICATION_PATH . '/../library/Facebook/Facebook.php';

class FacebookController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        // action body
        $signed_request = $this->_getParam('signed_request', null);
        if (empty($signed_request)) {
            throw new Facebook_Model_FacebookException('Security Error');
        }
        $this->_FB = new Facebook_Model_Facebook($signed_request);
        // if the user has not installed,
        // redirect to the allow URL
        if (!$this->_FB->hasInstalled) {
            $this->_helper->layout->disableLayout();
            $this->view->redirect_url = Facebook_Model_Facebook::FACEBOOK_ALLOW_URL . '?client_id=' . Facebook_Model_Facebook::FACEBOOK_APP_ID . '&redirect_uri=' . $_SERVER['HTTP_REFERER'];
            $this->_helper->viewRenderer->setScriptAction('redirect');
        } else {
            $this->view->signedRequest = $this->_FB->getSignedRequest();
            $this->view->fbid = $this->_FB->fbData['user_id'];
            $fbUserInfo = $this->_FB->getUserInfo();
            Zend_Debug::debug($fbUserInfo);
        }
    }

}

