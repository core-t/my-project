<?php

class Admin_LoginController extends Zend_Controller_Action {

    public function init() {
        Zend_Session::start();
    }

    public function indexAction() {
        $this->view->form = new Admin_Form_Login();

        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                $auth = Zend_Auth::getInstance();
                $auth->setStorage(new Zend_Auth_Storage_Session($this->getRequest()->getParam('module')));

                $result = $auth->authenticate($this->getAuthAdapter($this->view->form->getValues()));

                if (!$result->isValid()) {
                    $this->view->form->setDescription('Wprowadzono błędne dane');
                } else {
                    $this->_redirect('/admin');
                }
            }
        }

        $this->_helper->layout->setLayout('admin_login');
        $this->view->headLink()->prependStylesheet('/css/core-t_admin_login.css');

        $this->view->copyright();
    }

    private function getAuthAdapter($params) {
        $authAdapter = new Zend_Auth_Adapter_DbTable(
                        Zend_Db_Table_Abstract::getDefaultAdapter(),
                        'player',
                        'login',
                        'password',
                        'MD5(?) AND admin = true'
        );
        $authAdapter->setIdentity($params['login']);
        $authAdapter->setCredential($params['haslo']);
        return $authAdapter;
    }

    public function logoutAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();
        Zend_Session::destroy(true);
        $this->_redirect('/admin/login');
    }

}

