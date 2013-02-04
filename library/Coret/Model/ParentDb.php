<?php

class Coret_Model_ParentDb extends Zend_Db_Table_Abstract {

    /**
     *
     * @var type
     */
    protected $_id;

    /**
     *
     * @var type
     */
    protected $_params;

    /**
     *
     * @var type
     */
    protected $_db;

    /**
     *
     * @param array $params
     * @param type $id
     */
    public function __construct(Array $params, $id = 0) {
        $this->_id = intval($id);
        $this->_params = $params;
        $this->_db = $this->getDefaultAdapter();
    }

    /**
     *
     * @param type $dane
     * @return type
     */
    public function insertElement($dane) {
        $this->_db->insert($this->_name, $dane);
        return $this->_db->lastInsertId();
    }

    /**
     *
     * @param type $dane
     * @return type
     */
    public function updateElement($dane) {
        $where = array(
            $this->_db->quoteInto($this->_primary . ' = ?', $this->_id)
        );

        $where = $this->addWhere($where);

        return $this->_db->update($this->_name, $dane, $where);
    }

    /**
     *
     * @return type
     */
    protected function getSelect() {
        $select = $this->_db->select()
                ->from($this->_name);

        $select = $this->addSelectWhere($select);

        return $select;
    }

    /**
     *
     * @param type $id
     * @return type
     */
    public function getList($id = 0) {
        $select = $this->getSelect();
        return $this->_db->fetchAll($select);
    }

    /**
     *
     * @return \Zend_Paginator_Adapter_DbSelect
     */
    public function getPagination() {
        $select = $this->getSelect();
        return new Zend_Paginator_Adapter_DbSelect($select);
    }

    /**
     *
     * @return type
     */
    public function getElement() {
        $select = $this->_db->select()
                ->from($this->_name)
                ->where($this->_name . '.' . $this->_primary . ' = ?', $this->_id);
        $select = $this->addJoin($select);

        $select = $this->addSelectWhere($select);

        return $this->_db->fetchRow($select);
    }

    /**
     *
     * @param type $select
     * @return type
     */
    public function addJoin($select) {
        return $select;
    }

    public function addSelectWhere($select) {
        if (isset($this->_params['controller']) && $this->_params['controller']) {
            $select->where($this->_name . '.controller = ?', $this->_params['controller']);
        }

        if (isset($this->_params['action']) && $this->_params['action']) {
            $select->where($this->_name . '.action = ?', $this->_params['action']);
        }
        return $select;
    }

    public function addWhere($where) {
        if (isset($this->_params['controller']) && $this->_params['controller']) {
            $where[] = $this->_db->quoteInto('controller = ?', $this->_params['controller']);
        }

        if (isset($this->_params['action']) && $this->_params['action']) {
            $where[] = $this->_db->quoteInto('action = ?', $this->_params['action']);
        }
        return $where;
    }

    /**
     *
     * @return type
     */
    public function deleteElement() {
        $where = array(
            $this->_db->quoteInto($this->_primary . ' = ?', $this->_id)
        );

        $where = $this->addWhere($where);

        return $this->_db->delete($this->_name, $where);
    }

    /**
     *
     * @return type
     */
    public function getColumns() {
        return $this->_columns;
    }

    /**
     *
     * @param array $post
     * @return array
     */
    public function prepareData(Array $post) {
        $data = array();

        for ($i = 0; $i < count($this->_columns); $i++)
        {
            $column = key($this->_columns);

            if (isset($post[$column])) {
                if ($column == 'image' AND !$post[$column])
                    continue;
                $data[$column] = $post[$column];
            }

            next($this->_columns);
        }

        return $data;
    }

    /**
     *
     * @param type $data
     * @return type
     */
    public function addDataForInsert($data) {
        if (isset($this->_params['controller']) && $this->_params['controller']) {
            $data['controller'] = $this->_params['controller'];
        }

        if (isset($this->_params['action']) && $this->_params['action']) {
            $data['action'] = $this->_params['action'];
        }
        return $data;
    }

}

