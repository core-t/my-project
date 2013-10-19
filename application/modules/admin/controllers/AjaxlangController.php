<?php

class Admin_AjaxlangController extends Coret_Controller_BackendAjax
{

    protected $_id;
    protected $_modelClassName;

    public function init()
    {
        parent::init();

        $this->_id = $this->_request->getParam('id');
        $id_lang = $this->_request->getParam('id_lang');
        $this->_modelClassName = $this->_request->getParam('c');

        if (empty($this->_id)) {
            throw new Exception('There is no id');
        }
        if (empty($id_lang)) {
            throw new Exception('There is no id_lang');
        }
        if (empty($this->_modelClassName)) {
            throw new Exception('There is no class_name');
        }

        $this->params = array('id_lang' => $id_lang);
        if ($this->_request->getParam('id_kategoria')) {
            $this->params['id_kategoria'] = $this->_request->getParam('id_kategoria');
        }
        if ($this->_request->getParam('id_podkategoria')) {
            $this->params['id_podkategoria'] = $this->_request->getParam('id_podkategoria');
        }

        $this->_modelClassName = 'Admin_Model_' . ucfirst($this->_modelClassName);
    }

    public function saveAction()
    {
        $model = new $this->_modelClassName($this->params, $this->_id);
        $model->save($this->_request->getParams());

        echo Zend_Json::encode($model->getElement());
    }

    public function getAction()
    {
        $model = new $this->_modelClassName($this->params, $this->_id);
        echo Zend_Json::encode($model->getElement());
    }

}
