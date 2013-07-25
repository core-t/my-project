<?php

abstract class Coret_Controller_Backend extends Zend_Controller_Action
{

    public $params = array();
    protected $itemCountPerPage = 20;

    public function init()
    {
        parent::init();

        Zend_Session::start();
        Zend_Auth::getInstance()->setStorage(new Zend_Auth_Storage_Session($this->getRequest()->getParam('module')));
        if (!Zend_Auth::getInstance()->hasIdentity()) {
            $this->_redirect('/admin/login');
        }
        $this->_helper->layout->setLayout('admin');

        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/core-t_admin.css');
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/sceditor/themes/default.min.css');

        $this->view->jquery();

        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/core-t_admin.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jquery.sceditor.min.js');

        $this->view->headMeta()->appendHttpEquiv('Content-Language', 'pl');

        $this->view->menu();
        $this->view->copyright();

        $this->view->controllerName = $this->slugify(strtolower(str_replace(' ', '', $this->view->title)));
    }

    private function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('#[^\\pL\d]+#u', '-', $text);

        // trim
        $text = trim($text, '-');

        $text = str_replace('ł', 'l', $text);
        $text = str_replace('ś', 's', $text);
        $text = str_replace('ć', 'c', $text);

        // transliterate
        if (function_exists('iconv')) {
//            $text = iconv('UTF-8', 'ASCII//TRANSLIT', utf8_encode($text));
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        }

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('#[^-\w]+#', '', $text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array())
    {
        parent::__construct($request, $response, $invokeArgs);
    }

    public function indexAction()
    {
        $className = 'Admin_Model_' . ucfirst($this->view->controllerName);
        $this->indexEnding($className);
    }

    protected function indexEnding($className)
    {
        $m = new $className($this->params);

        $this->view->kolumny = $m->getColumnsAll();
        $this->view->primary = $m->getPrimary();

        $this->view->paginator = new Zend_Paginator($m->getPagination($this->_request->getParam('sort'), $this->_request->getParam('order')));
        $this->view->paginator->setCurrentPageNumber($this->_request->getParam('page'));
        $this->view->paginator->setItemCountPerPage($this->itemCountPerPage);
    }

    public function addAction()
    {
        $className = 'Admin_Model_' . ucfirst($this->view->controllerName);
        $model = new $className($this->params);

        $className = 'Admin_Form_' . ucfirst($this->view->controllerName);
        if (class_exists($className)) {
            $this->view->form = new $className();
        } else {
            $this->addForm($model->getColumnsAll(), $model->isLang());
        }

        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                try {
                    $model->save($this->view->form->getValues());
                    $this->_redirect($this->view->url());
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

    public function deleteAction()
    {
        $id = $this->_request->getParam('id');
        if (!Zend_Validate::is($id, 'Digits')) {
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

    public function editAction()
    {
        $id = $this->_request->getParam('id');
        if (!Zend_Validate::is($id, 'Digits')) {
            throw new Exception('Brak id');
        }

        $className = 'Admin_Form_' . ucfirst($this->view->controllerName);
        if (class_exists($className)) {
            $this->view->form = new $className();
        }

        if ($this->_request->isPost()) {
            $this->editHandlePost($id);
        } else {
            $this->editHandleElse($id);
        }
        $this->addGalleryForm();
    }

    protected function editHandlePost($id)
    {
        $className = 'Admin_Model_' . ucfirst($this->view->controllerName);
        $model = new $className($this->params, $id);

        $this->addForm($model->getColumnsAll(), $model->isLang(), $id);

        if ($this->view->form->isValid($this->_request->getPost())) {
            try {
                $model->save($this->view->form->getValues());
                $this->_redirect('/admin/' . Zend_Controller_Front::getInstance()->getRequest()->getControllerName());
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

        $this->addForm($model->getColumnsAll(), $model->isLang(), $id);

        $element = $model->getElement();
        $this->view->form->populate($element);
        $this->view->form->addElement('submit', 'submit', array('label' => 'Zmień'));
        $this->view->form->setDefault('id', $id);

        if (isset($element['image'])) {
            $this->view->img = $this->view->controllerName . '_' . $id . '.jpg';
        }

        $columnsLang = $model->getColumnsLang();
        if ($columnsLang) {
            $this->addLangForm($id, $columnsLang);
        }
    }

    protected function addForm($columns, $isLang, $id = null)
    {
        if (isset($this->view->form)) {
            return;
        }
        $this->view->form = new Zend_Form();

        foreach ($columns as $key => $row) {
            if (isset($row['active']['form']) && !$row['active']['form']) {
                continue;
            }
            $className = 'Coret_Form_' . ucfirst($row['typ']);
            $attributes = array('name' => $key);
            if (isset($row['label'])) {
                $attributes['label'] = $row['label'];
            }
            if (isset($row['required'])) {
                $attributes['required'] = $row['required'];
            }
            if (isset($row['validators'])) {
                $attributes['validators'] = $row['validators'];
            }
            $f = new $className($attributes);
            $this->view->form->addElements($f->getElements());
        }

        if ($id) {
            $fId = new Coret_Form_Id();
            $this->view->form->addElements($fId->getElements());

            $this->view->form->setDefault('id', $id);
        }

        if ($isLang) {
            $f = new Coret_Form_IdLang();
            $this->view->form->addElements($f->getElements());
            $this->view->form->setDefault('id_lang', 1);
        }
    }

    protected function addLangForm($id, $columnsLang)
    {
        $this->view->formLang = new Zend_Form();
        $fLang = new Coret_Form_Lang();
        $this->view->formLang->addElements($fLang->getElements());

        foreach ($columnsLang as $key => $row) {

            $className = 'Coret_Form_' . ucfirst($row['typ']);
            $f = new $className(array('name' => $key, 'label' => $row['label']));
            $this->view->formLang->addElements($f->getElements());
        }

        $fId = new Coret_Form_Id();
        $this->view->formLang->addElements($fId->getElements());

        $this->view->formLang->setDefault('id', $id);
        $this->view->formLang->setAttrib('id', 'lang');
    }

    protected function addGalleryForm()
    {

    }
}
