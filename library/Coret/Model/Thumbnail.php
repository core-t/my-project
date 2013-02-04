<?php

class Coret_Model_Thumbnail {

    protected $_uploadDir = '/../public/upload/';
    protected $_thumbSize = array('width' => 253, 'height' => 137);
    protected $_parentId;
    protected $_image;
    protected $_imgId;
    protected $_type;

    public function __construct($params, $parentId, $image, $id_img = null) {
        if (isset($params['controller'])) {
            $this->_controllerName = $params['controller'];
        }
        if (isset($params['action'])) {
            $this->_actionName = $params['action'];
        }

        $this->_parentId = $parentId;
        $this->_image = $image;
        $this->_imgId = $id_img;

        $this->_uploadDir = APPLICATION_PATH . $this->_uploadDir;
    }

    public function getType() {
        return $this->_type;
    }

    public function setType($type) {
        $this->_type = $type;
    }

    public function createThumbnail($width, $height, $prefix = '', $adaptiveResize = 0, $q = 80) {
        $this->setType(substr($this->_image, -3));

        $sourcePath = $this->_uploadDir . $this->_image;

        if (isset($this->_controllerName)) {
            $controllerName = $this->_controllerName . '_';
        }

        if (isset($this->_actionName)) {
            $actionName = '_' . $this->_actionName;
        } else {
            $actionName = '';
        }

        if ($this->_parentId) {
            $id_parent = $this->_parentId;
        }

        if ($this->_imgId) {
            $id_img = '_' . $this->_imgId;
        }

        if ($prefix) {
            $prefix = $prefix . '_';
        }

        $destPath = $this->_uploadDir . $prefix . $controllerName . $id_parent . $id_img . $actionName . '.' . $this->getType();

        $thumb = new PhpThumb_GdThumb($sourcePath);

        if ($adaptiveResize) {
            $thumb->adaptiveResize($width, $height);
        } else {
            $thumb->resize($width, $height);
        }

        if ($this->getType() == 'jpg') {
            $thumb->setOptions(array('jpegQuality' => $q));
        }
        $thumb->save($destPath, $this->getType());
    }

}

