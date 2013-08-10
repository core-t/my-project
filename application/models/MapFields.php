<?php

class Application_Model_MapFields extends Game_Db_Table_Abstract
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
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->order(array('y', 'x'));

        $all = $this->selectAll($select);

        $mapFields = array();

        foreach ($all as $val) {
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

