<?php

abstract class Coret_Controller_Backend extends Zend_Controller_Action
{

    public $params = null;

    public function init()
    {
        parent::init();

        Zend_Session::start();
        Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session($this->getRequest()->getParam('module')));
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_redirect('/admin/login');
        }
        $this->_helper->layout->setLayout('core-t_admin');

        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/core-t_admin.css');

        $this->view->jquery();

        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/core-t_admin.js');
//        $language = $this->getRequest()->getParam('lang');
        $language = 'pl';

        $this->view->headMeta()->appendHttpEquiv('Content-Language', $language);

        $this->view->menu();
        $this->view->copyright();

        $this->view->controllerName = strtolower(str_replace(' ', '', $this->view->title));

//        new Application_View_Helper_Lang($language);
    }

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);
    }

    public function indexAction()
    {
        $className = 'Admin_Model_' . ucfirst($this->view->controllerName);
        $m = new $className($this->params);

        $this->view->kolumny = $m->getColumns();

        $this->view->paginator = new Zend_Paginator($m->getPagination());
        $this->view->paginator->setCurrentPageNumber($this->_request->getParam('page'));
        $this->view->paginator->setItemCountPerPage(10);
        $this->view->primary = $m->getPrimary();
    }

    public function addAction()
    {
        $className = 'Admin_Form_' . ucfirst($this->view->controllerName);
        $this->view->form = new $className();
        $this->view->form->addElement('submit', 'submit', array('label' => 'Dodaj'));

        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                $className = 'Admin_Model_' . ucfirst($this->view->controllerName);
                $model = new $className($this->params);
                try {
                    $model->handleElement($this->view->form->getValues());
                    $this->_redirect('/admin/' . $this->view->controllerName);
                } catch (Exception $e) {
                    echo $e->getMessage();
                    exit;
                }
            } else {
                $this->view->form->addElement('submit', 'submit', array('label' => 'Popraw'));
            }
        } else {
            $this->view->form->addElement('submit', 'submit', array('label' => 'Dodaj'));
        }
    }

    public function editAction()
    {
        $id = $this->_request->getParam('id');
        if (!$id) {
            throw new Exception('Brak id');
        }

        $className = 'Admin_Form_' . ucfirst($this->view->controllerName);
        $this->view->form = new $className();

        if ($this->_request->isPost()) {
            $this->editHandlePost($id);
        } else {
            $this->editHandleElse($id);
        }
    }

    public function deleteAction()
    {
        $id = $this->_request->getParam('id');
        if (!$id) {
            throw new Exception('Brak id');
        }

        if ($this->_request->getParam('yes')) {
            $className = 'Admin_Model_' . ucfirst($this->view->controllerName);
            $model = new $className($this->params, $id);
            try {
                $model->deleteElement();
                $this->_redirect('/admin/' . $this->view->controllerName);
            } catch (Exception $e) {
                echo $e->getMessage();
                exit;
            }
        } else {
            $this->view->ask = true;
        }
    }

    protected function editHandlePost($id)
    {
        if ($this->view->form->isValid($this->_request->getPost())) {
            $className = 'Admin_Model_' . ucfirst($this->view->controllerName);
            $model = new $className($this->params, $id);
            try {
                $model->handleElement($this->view->form->getValues());
                $this->_redirect('/admin/' . $this->view->controllerName);
            } catch (Exception $e) {
                echo $e->getMessage();
                exit;
            }
        } else {
            $this->view->form->addElement('submit', 'submit', array('label' => 'Popraw'));
        }
    }

    protected function editHandleElse($id)
    {
        $className = 'Admin_Model_' . ucfirst($this->view->controllerName);
        $model = new $className($this->params, $id);

        $element = $model->getElement();
        $this->view->form->populate($element);
        $this->view->form->addElement('submit', 'submit', array('label' => 'Zmień'));
        $this->view->form->setDefault('id', $id);
        if (isset($element['image'])) {
            $this->view->img = $this->view->controllerName . '_' . $id . '.jpg';
        }
    }

}
