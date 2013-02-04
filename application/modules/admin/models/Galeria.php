<?php

class Admin_Model_Galeria extends Coret_Model_ParentDb {

    protected $_name = 'Galeria';
    protected $_primary = 'id';
    protected $_width = 678;
    protected $_height = 445;
    protected $_widthSmall = 328;
    protected $_heightSmall = 219;

    public function handleElement($post, $parentId) {
        $dane = array(
            'identyfikator' => Zend_Auth::getInstance()->getIdentity(),
            'image' => $post['image' . $this->_id]
        );

        if ($this->_chechIfExist($parentId)) {

            $dane['id_parent'] = $parentId;

            $this->updateElement($dane);

            return $this->createThumbnails($dane['image'], $parentId);
        } else {
            if (isset($this->_params['controller']) && $this->_params['controller']) {
                $dane['controller'] = $this->_params['controller'];
            }

            if (isset($this->_params['action']) && $this->_params['action']) {
                $dane['action'] = $this->_params['action'];
            }

            $dane['id'] = $this->_id;
            $dane['id_parent'] = $parentId;

            $this->insertElement($dane);

            return $this->createThumbnails($dane['image'], $parentId);
        }
    }

    /**
     *
     * @param type $dane
     * @return type
     */
    public function updateElement($dane) {
        $where = array(
            $this->_db->quoteInto($this->_primary . ' = ?', $this->_id),
            $this->_db->quoteInto('id_parent = ?', $dane['id_parent'])
        );

        if (isset($this->_params['controller']) && $this->_params['controller']) {
            $where[] = $this->_db->quoteInto('controller = ?', $this->_params['controller']);
        }

        if (isset($this->_params['action']) && $this->_params['action']) {
            $where[] = $this->_db->quoteInto('action = ?', $this->_params['action']);
        }

        return $this->_db->update($this->_name, $dane, $where);
    }

    private function createThumbnails($image, $parentId) {
        $cT = new Coret_Model_Thumbnail($this->_params, $parentId, $image, $this->_id);
        $cT->createThumbnail($this->_width, $this->_height);
        $cT->createThumbnail($this->_widthSmall, $this->_heightSmall, 'small');
        return $cT->getType();
    }

    private function _chechIfExist($parentId) {
        $select = $this->_db->select()
                ->from($this->_name, 'id')
                ->where($this->_primary . ' = ?', $this->_id)
                ->where('id_parent = ?', $parentId);

        return $this->_db->fetchOne($select);
    }

    public function getList($id = 0) {
        $select = $this->_db->select()
                ->from($this->_name, array('id', 'image'))
                ->where('id_parent = ?', $id)
                ->order('id');

        if (isset($this->_params['controller']) && $this->_params['controller']) {
            $select->where('controller = ?', $this->_params['controller']);
        }

        if (isset($this->_params['action']) && $this->_params['action']) {
            $select->where('action = ?', $this->_params['action']);
        }

        return $this->_db->fetchAll($select);
    }

    public function deleteLastElement($parentId) {
        $select = $this->_db->select()
                ->from($this->_name, new Zend_Db_Expr('max(id)'))
                ->where('id_parent = ?', $parentId);

        if (isset($this->_params['controller']) && $this->_params['controller']) {
            $select->where = $this->_db->quoteInto('controller = ?', $this->_params['controller']);
        }

        if (isset($this->_params['action']) && $this->_params['action']) {
            $select->where = $this->_db->quoteInto('action = ?', $this->_params['action']);
        }

        $where = 'id = ' . $this->_db->fetchOne($select);

        return $this->_db->delete($this->_name, $where);
    }

}

