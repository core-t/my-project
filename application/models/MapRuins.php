<?php

class Application_Model_MapRuins extends Coret_Db_Table_Abstract
{
    protected $_name = 'mapruins';
    protected $_primary = 'mapRuinId';
//    protected $_sequence = "map_mapId_seq";
    protected $_db;
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

    public function getMapRuins()
    {
        $select = $this->_db->select()
            ->from($this->_name, array($this->_primary, 'x', 'y'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId);

        $ret = array();

        foreach ($this->selectAll($select) as $val) {
            $ret[$val[$this->_primary]] = array(
                'x' => $val['x'],
                'y' => $val['y']
            );
        }

        return $ret;
    }

    public function add($x, $y)
    {
        $data = array(
            'mapId' => $this->mapId,
            'x' => $x,
            'y' => $y
        );
        $this->insert($data);
    }
}

