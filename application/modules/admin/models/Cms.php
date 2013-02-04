<?php

class Admin_Model_Cms extends Coret_Model_ParentDb {

    protected $_name = 'CMS';
    protected $_primary = 'id';
    protected $_width = 800;
    protected $_height = 800;
    protected $_widthSmall = 200;
    protected $_heightSmall = 200;

    public function handleElement($post) {
        $dane = array(
            'identyfikator' => Zend_Auth::getInstance()->getIdentity()
        );

        for ($i = 0; $i < count($this->_columns); $i++)
        {
            $column = key($this->_columns);
            if (isset($post[$column])) {
                if ($column == 'image' AND !$post[$column])
                    continue;
                $dane[$column] = $post[$column];
            }
            next($this->_columns);
        }

        if ($post['id']) {
            $id = $post['id'];
            if (isset($dane['image'])) {
                $this->createThumbnails($id, $dane['image']);
            }
            $this->updateElement($dane);
        } else {
            if (isset($this->_params['controller']) && $this->_params['controller']) {
                $dane['controller'] = $this->_params['controller'];
            }

            if (isset($this->_params['action']) && $this->_params['action']) {
                $dane['action'] = $this->_params['action'];
            }

            $id = $this->insertElement($dane);
            if ($id) {
                if (isset($dane['image'])) {
                    $this->createThumbnails($id, $dane['image']);
                }
            }
        }
    }

    private function createThumbnails($id, $image) {
        $cT = new Coret_Model_Thumbnail($this->_params, $id, $image);
        $cT->createThumbnail($this->_width, $this->_height);
        $cT->createThumbnail($this->_widthSmall, $this->_heightSmall, 'small');
    }

}

