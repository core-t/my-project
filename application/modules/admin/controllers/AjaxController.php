<?php

class Admin_AjaxController extends Coret_Controller_BackendAjax {

    protected $_id;

    public function init() {
        parent::init();

        $this->_id = $this->_request->getParam('id');

        if (empty($this->_id)) {
            throw new Exception('There is no id');
        }
    }

    public function addimageAction() {
        $imageId = $this->_request->getParam('image');

        if (empty($imageId)) {
            throw new Exception('There is no id');
        }

        $params = array(
            'controller' => 'portfolio'
        );

        $form = new Admin_Form_Galeria();
        $image = new Admin_Form_Image(array('numer' => $imageId, 'required' => true));
        $form->addElements($image->getElements());

        $model = new Admin_Model_Galeria($params, $imageId);

        try {
            $type = $model->handleElement($form->getValues(), $this->_id);
            $data = array('type' => $type);
        } catch (Exception $e) {
            $data = array('error' => 'Failed to save');
            throw new Exception($e);
        }
        echo Zend_Json::encode($data);
    }

    public function delimageAction() {
        $params = array(
            'controller' => 'portfolio'
        );

        $model = new Admin_Model_Galeria($params);

        try {
            $model->deleteLastElement($this->_id);
            $data = array('status' => 'ok');
        } catch (Exception $e) {
            $data = array('status' => 'error');
            throw new Exception($e);
        }
        echo Zend_Json::encode($data);
    }

}
