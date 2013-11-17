<?php

class Application_Model_SoldiersCreated extends Coret_Db_Table_Abstract
{
    protected $_name = 'soldierscreated';
    protected $_primary = 'soldierscreatedId';
    protected $_sequence = '';
    protected $_gameId;

    public function __construct($gameId, $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function countCreated($playersInGameColors)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('count(*)', 'playerId'))
            ->where('"gameId" = ?', $this->_gameId)
            ->group('playerId');

        $array = array();

        foreach ($this->selectAll($select) as $v) {
            $array[$playersInGameColors[$v['playerId']]] = $v['count'];
        }

        return $array;
    }

    public function getCreated()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('unitId', 'playerId'))
            ->where('"gameId" = ?', $this->_gameId);

        $array = array();

        foreach ($this->selectAll($select) as $v) {
            $array[$v['playerId']][] = $v['unitId'];
        }

        return $array;
    }

    public function add($unitId, $playerId)
    {
        $data = array(
            'unitId' => $unitId,
            'gameId' => $this->_gameId,
            'playerId' => $playerId
        );

        $this->insert($data);
    }
}

