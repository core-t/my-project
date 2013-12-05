<?php

class Application_Model_MapTowers extends Coret_Db_Table_Abstract
{
    protected $_name = 'maptowers';
    protected $_primary = 'mapTowerId';
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

    public function getMapTowers()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('mapTowerId', 'x', 'y'))
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId);

        $mapTowers = array();

        foreach ($this->selectAll($select) as $val) {
            $mapTowers[$val['mapTowerId']] = array(
                'x' => $val['x'],
                'y' => $val['y']
            );
        }

        return $mapTowers;
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

