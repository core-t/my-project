<?php

class Coret_Model_ParentDb extends Zend_Db_Table_Abstract
{

    /**
     * @var int
     */
    protected $_id;

    /**
     * @var array
     */
    protected $_params;

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    protected $_name;
    protected $_primary;
    protected $_columns;

    /**
     * @param array $params
     * @param int $id
     */
    public function __construct(Array $params = array(), $id = 0)
    {
        $this->_id = intval($id);
        $this->_params = $params;
        $this->_db = $this->getDefaultAdapter();
        if (Zend_Registry::get('config')->resources->db->adapter == 'pdo_mysql') {
            $this->_db->query("SET NAMES 'utf8'");
            $this->_db->query("set character set 'utf8'");
        }
    }

    public function create()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . $this->_name . '` (
            `' . $this->_primary . '` bigint(20) unsigned NOT NULL AUTO_INCREMENT,';
        foreach ($this->_columns as $columnName => $vals) {
            switch ($vals['typ']) {
                case 'varchar':
                    $sql .= '`' . $columnName . '` varchar(256) NOT NULL,';
                    break;
                case 'text':
                    $sql .= '`' . $columnName . '` text NOT NULL,';
                    break;
                case 'checkbox':
                    $sql .= '`' . $columnName . '` bool NOT NULL,';
                    break;
            }
        }
        $sql .= 'PRIMARY KEY(`' . $this->_primary . '`),
            UNIQUE KEY `' . $this->_primary . '` (`' . $this->_primary . '`)
            ) ENGINE = MyISAM DEFAULT CHARSET = utf8 AUTO_INCREMENT = 1';
        $this->_db->query($sql);
    }

    /**
     * @param $post
     * @return bool|mixed|type
     */
    public function save($post)
    {
        $dane = $this->prepareData($post);
        if (isset($post['id']) && $post['id']) {
            $this->_id = $post['id'];
            $dane['data'] = $this->handleImg($dane, $post['id']);
            if ($dane['data']) {
                $this->updateElement($dane['data']);
            }

            if ($dane['data_lang'] && $post['id_lang']) {
                if ($this->checkIfLangExist($post['id'], $post['id_lang'])) {
                    $dane['data_lang']['id_lang'] = $post['id_lang'];
                    return $this->updateElementLang($dane['data_lang'], $post['id']);
                } else {
                    $dane['data_lang'][$this->_primary] = $post['id'];
                    $dane['data_lang']['id_lang'] = $post['id_lang'];
                    return $this->insertElementLang($dane['data_lang']);
                }
            }
        } else {
            $dane['data'] = $this->addDataInsert($dane['data']);
            $id = $this->insertElement($dane['data']);
            if ($id) {
                $this->_id = $id;
                $dane['data'] = $this->handleImg($dane, $id);
                if ($dane['data']) {
                    $this->updateElement($dane['data']);
                }
                if ($dane['data_lang']) {
                    $dane['data_lang'][$this->_primary] = $id;
                    return $this->insertElementLang($dane['data_lang']);
                }
                return true;
            }
        }
    }

    protected function addDataInsert($dane)
    {
        if (isset($this->_params['controller']) && $this->_params['controller']) {
            $dane['controller'] = $this->_params['controller'];
        }

        if (isset($this->_params['action']) && $this->_params['action']) {
            $dane['action'] = $this->_params['action'];
        }
        return $dane;
    }

    protected function handleImg($dane, $id)
    {
        foreach ($dane['data_img'] as $k => $v) {
            $mThumb = new Coret_Model_Thumbnail(array(), $id, $v);

            $mThumb->createThumbnail($this->_columns[$k]['resize']['width'], $this->_columns[$k]['resize']['height'], $k . '_' . $this->_columns[$k]['prefix']);

            if (isset($this->_params['width']) && isset($this->_params['height'])) {
                $mThumb->createThumbnail($this->_params['width'], $this->_params['height'], 'big_' . $k . '_' . $this->_columns[$k]['prefix']);
            }

            $dane['data'][$k] = $mThumb->getType();

        }
        return $dane['data'];
    }

    /**
     * @param $dane
     * @return string
     */
    public function insertElement($dane)
    {
        $this->_db->insert($this->_name, $dane);
        return $this->_db->lastInsertId();
    }

    /**
     * @param $dane
     * @return string
     */
    public function insertElementLang($dane)
    {
        if (!isset($dane['id_lang']) || !$dane['id_lang']) {
            $dane['id_lang'] = 1;
        }
        $this->_db->insert($this->_name . '_Lang', $dane);
        return $this->_db->lastInsertId();
    }

    /**
     * @param $dane
     * @return int
     */
    public function updateElement($dane)
    {
        $where = array(
            $this->_db->quoteInto($this->_primary . ' = ?', $this->_id)
        );
        $where = $this->addWhere($where);
        return $this->_db->update($this->_name, $dane, $where);
    }

    /**
     * @param $dane
     * @return mixed
     */
    public function updateElementLang($dane, $id)
    {
        $where = array(
            $this->_db->quoteInto($this->_primary . ' = ?', $id)
        );

        $where = $this->addWhereLang($where, $dane);

        return $this->_db->update($this->_name . '_Lang', $dane, $where);
    }

    /**
     * @param array $columns
     * @return mixed
     */
    protected function getSelect($sort, $order, array $columns, array $columns_lang)
    {
        $select = $this->_db->select();

        if ($columns) {
            $select->from($this->_name, $columns);
        } else {
            $select->from($this->_name);
        }

        $select = $this->addJoin($select, $columns_lang);
        $select = $this->addSelectWhereLang($select);
        $select = $this->addSelectWhere($select);
        $select = $this->addGroup($select);
        $select = $this->addOrder($sort, $order, $select);

        return $select;
    }

    /**
     * @param array $columns
     * @return array
     */
    public function getList($sort = '', $order = '', array $columns = array(), array $columns_lang = array())
    {
        $select = $this->getSelect($sort, $order, $columns, $columns_lang);
        return $this->_db->fetchAll($select);
    }

    /**
     * @param array $columns
     * @return Zend_Paginator_Adapter_DbSelect
     */
    public function getPagination($sort, $order, array $columns = array(), array $columns_lang = array())
    {
        $select = $this->getSelect($sort, $order, $columns, $columns_lang);
        return new Zend_Paginator_Adapter_DbSelect($select);
    }

    /**
     * @return mixed
     */
    public function getElement($id = null)
    {
        if (!$id) {
            $id = $this->_id;
        }
        $select = $this->_db->select()
            ->from($this->_name)
            ->where($this->_name . ' . "' . $this->_primary . '" = ?', $id);

        $select = $this->addSelectWhereLang($select);
        $select = $this->addSelectWhere($select);
        $select = $this->addJoin($select);

        $result = $this->_db->fetchRow($select);
        if (is_array($result)) {
            return $result;
        }

        return array();
    }

    /**
     * @param $select
     * @return mixed
     */
    protected function addJoin($select, $columns_lang = array())
    {
        if ($columns_lang) {
            return $select->join($this->_name . '_Lang', $this->_name . ' . ' . $this->_primary . ' = ' . $this->_name . '_Lang . ' . $this->_primary, $columns_lang);
        } elseif (isset($this->_columns_lang) && $this->_columns_lang) {
            $columns_lang = array();
            foreach (array_keys($this->_columns_lang) as $column) {
                $columns_lang[] = $column;
            }
            return $select->join($this->_name . '_Lang', $this->_name . ' . ' . $this->_primary . ' = ' . $this->_name . '_Lang . ' . $this->_primary, $columns_lang);
        }
        return $select;
    }

    /**
     * @param $select
     * @return mixed
     */
    protected function addSelectWhere($select)
    {
        if (isset($this->_params['controller']) && $this->_params['controller']) {
            $select->where($this->_name . ' . controller = ?', $this->_params['controller']);
        }

        if (isset($this->_params['action']) && $this->_params['action']) {
            $select->where($this->_name . ' . action = ?', $this->_params['action']);
        }

        return $select;
    }

    /**
     * @param $select
     * @return mixed
     */
    protected function addSelectWhereLang($select)
    {
        if (isset($this->_columns_lang) && $this->_columns_lang && isset($this->_params['id_lang']) && $this->_params['id_lang']) {
            $select->where($this->_name . '_Lang . id_lang = ?', $this->_params['id_lang']);
        }

        return $select;
    }

    /**
     * @param $where
     * @return array
     */
    protected function addWhere($where)
    {
        if (isset($this->_params['controller']) && $this->_params['controller']) {
            $where[] = $this->_db->quoteInto('controller = ?', $this->_params['controller']);
        }

        if (isset($this->_params['action']) && $this->_params['action']) {
            $where[] = $this->_db->quoteInto('action = ?', $this->_params['action']);
        }
        return $where;
    }

    /**
     * @param $where
     * @return array
     */
    protected function addWhereLang($where, $data)
    {
        $where[] = $this->_db->quoteInto('id_lang = ?', $data['id_lang']);
        return $where;
    }

    /**
     * @param $select
     * @return mixed
     */
    protected function addGroup($select)
    {
        return $select;
    }

    /**
     * @param $select
     * @return mixed
     */
    protected function addOrder($sort, $order, $select)
    {
        if ($sort) {
            if ($order) {
                $select->order($sort . ' ' . $order);
            } else {
                $select->order($sort);
            }
        } elseif (isset($this->_sort) && $this->_sort) {
            if (isset($this->_order) && $this->_order) {
                $select->order($this->_sort . ' ' . $order);
            } else {
                $select->order($this->_sort);
            }
        }

        return $select;
    }

    /**
     * @return int
     */
    public function deleteElement()
    {
        $where = array(
            $this->_db->quoteInto($this->_primary . ' = ?', $this->_id)
        );

        $where = $this->addWhere($where);

        return $this->_db->delete($this->_name, $where);
    }

    /**
     * @param array $post
     * @return array
     */
    protected function prepareData(Array $post)
    {
        $namespace = new Zend_Session_Namespace();
        $data = array(
            'data' => new Zend_Db_Expr('now()'),
            'id_administrator' => $namespace->id
        );
        $data_lang = array();
        $data_img = array();

        for ($i = 0; $i < count($this->_columns); $i++) {
            $column = key($this->_columns);

            if (isset($post[$column])) {
                if (isset($this->_columns[$column]['active']['db']) && !$this->_columns[$column]['active']['db']) {
                    next($this->_columns);
                    continue;
                }
                if ($this->_columns[$column]['typ'] == 'image') {
                    if ($post[$column]) {
                        $data_img[$column] = $post[$column];
                    }
                    next($this->_columns);
                    continue;
                }
                $data[$column] = $post[$column];
            }

            next($this->_columns);
        }

        if (isset($this->_columns_lang) && $this->_columns_lang) {
            for ($i = 0; $i < count($this->_columns_lang); $i++) {
                $column_lang = key($this->_columns_lang);

                if (isset($post[$column_lang])) {
                    if (!$post[$column_lang]) {
                        next($this->_columns_lang);
                        continue;
                    }
                    $data_lang[$column_lang] = $post[$column_lang];
                }

                next($this->_columns_lang);
            }
        }

        return array('data' => $data, 'data_lang' => $data_lang, 'data_img' => $data_img);
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function addDataForInsert($data)
    {
        if (isset($this->_params['controller']) && $this->_params['controller']) {
            $data['controller'] = $this->_params['controller'];
        }

        if (isset($this->_params['action']) && $this->_params['action']) {
            $data['action'] = $this->_params['action'];
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getColumnsAll()
    {
        if (isset($this->_columns_lang) && $this->_columns_lang) {
            return array_merge($this->_columns, $this->_columns_lang);
        }
        return $this->_columns;
    }

    /**
     *
     * @return type
     */
    public function getColumnsLang()
    {
        if (isset($this->_columns_lang) && $this->_columns_lang) {
            return $this->_columns_lang;
        }
    }

    /**
     * @return bool
     */
    public function isLang()
    {
        if (isset($this->_columns_lang) && $this->_columns_lang) {
            return true;
        }
    }

    /**
     * @return mixed|null
     */
    public function getPrimary()
    {
        return $this->_primary;
    }

    /**
     * @param $id
     * @param $id_lang
     * @return mixed
     */
    protected function checkIfLangExist($id, $id_lang)
    {
        $select = $this->_db->select()
            ->from($this->_name . '_Lang', $this->_primary)
            ->where('id_lang = ?', $id_lang)
            ->where($this->_primary . ' = ?', $id);

        return $this->_db->fetchOne($select);
    }

}

