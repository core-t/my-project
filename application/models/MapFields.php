<?php

class Application_Model_MapFields extends Coret_Db_Table_Abstract
{
    protected $_name = 'mapfields';
    protected $_primary = 'mapId';
//    protected $_sequence = "map_mapId_seq";
    protected $mapId;

    public function __construct($mapId, $db = null)
    {
        $this->mapId = $mapId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getMapFields()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('x', 'y', 'type'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->order(array('y', 'x'));

        $mapFields = array();

        foreach ($this->selectAll($select) as $val) {
            $mapFields[$val['y']][$val['x']] = $val['type'];
        }

        return $mapFields;
    }

    public function add($x, $y, $type)
    {
        $data = array(
            'mapId' => $this->mapId,
            'x' => $x,
            'y' => $y,
            'type' => $type
        );

        $this->insert($data);
    }
}

