<?php

class Application_Model_Unit extends Coret_Db_Table_Abstract
{

    protected $_name = 'unit';
    protected $_primary = 'unitId';

    public function __construct($db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getAll()
    {
        $units = array();
//        $units = array(null);

        $select = $this->_db->select()
            ->from(array('a' => 'unit'))
            ->join(array('b' => 'unit_Lang'), 'a."unitId"=b."unitId"', 'name')
            ->where('id_lang = ?', Zend_Registry::get('config')->id_lang)
            ->order(array('special', 'attackPoints', 'defensePoints', 'numberOfMoves'));

        foreach ($this->selectAll($select) as $unit) {
            $select = $this->_db->select()
                ->from('unit_Lang', 'name')
                ->where('id_lang = ?', Zend_Registry::get('id_lang'))
                ->where($this->_db->quoteIdentifier('unitId') . ' = ?', $unit['unitId']);

            $unit['name_lang'] = $this->selectOne($select);

            $units[$unit['unitId']] = $unit;
        }

        return $units;
    }
}

