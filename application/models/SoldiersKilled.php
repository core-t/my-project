<?php

class Application_Model_SoldiersKilled extends Coret_Db_Table_Abstract
{
    protected $_name = 'soldierskilled';
    protected $_primary = 'soldierskilledId';
    protected $_sequence = 'soldierskilled_soldierskilledId_seq';
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

    public function countKilled($playersInGameColors)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('count(*)', 'winnerId'))
            ->where('"gameId" = ?', $this->_gameId)
            ->group('winnerId');

        $array = array();

        foreach ($this->selectAll($select) as $v) {
            if ($v['winnerId'] == 0) {
                continue;
            }
            $array[$playersInGameColors[$v['winnerId']]] = $v['count'];
        }

        return $array;
    }

    public function getKilled()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('unitId', 'winnerId'))
            ->where('"gameId" = ?', $this->_gameId);

        $array = array();

        foreach ($this->selectAll($select) as $v) {
            $array[$v['winnerId']][] = $v['unitId'];
        }

        return $array;
    }

    public function countLost($playersInGameColors)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('count(*)', 'loserId'))
            ->where('"gameId" = ?', $this->_gameId)
            ->group('loserId');

        $array = array();

        foreach ($this->selectAll($select) as $v) {
            $array[$playersInGameColors[$v['loserId']]] = $v['count'];
        }

        return $array;
    }

    public function getLost()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('unitId', 'loserId'))
            ->where('"gameId" = ?', $this->_gameId);

        $array = array();

        foreach ($this->selectAll($select) as $v) {
            $array[$v['loserId']][] = $v['unitId'];
        }

        return $array;
    }

    public function add($unitId, $winnerId, $loserId)
    {
        $data = array(
            'unitId' => $unitId,
            'gameId' => $this->_gameId,
            'winnerId' => $winnerId,
            'loserId' => $loserId
        );

        $this->insert($data);
    }
}

