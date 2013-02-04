<?php

abstract class Coret_Controller_BackendAjax extends Zend_Controller_Action {

    public function init() {
        parent::init();

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        Zend_Session::start();
        Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session($this->getRequest()->getParam('module')));
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            exit;
        }
    }

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        parent::__construct($request, $response, $invokeArgs);
    }

}
