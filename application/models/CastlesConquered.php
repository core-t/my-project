<?php

class Application_Model_CastlesConquered extends Coret_Db_Table_Abstract
{
    protected $_name = 'castlesconquered';
    protected $_primary = 'castlesconqueredId';
    protected $_sequence = 'castlesconquered_castlesconqueredId_seq';
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

    public function getAll()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('winnerId', 'loserId', 'castleId'))
            ->where('"gameId" = ?', $this->_gameId)
            ->order($this->_primary);
        return $this->selectAll($select);
    }

    public function countConquered($playersInGameColors)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('count(*)', 'winnerId'))
            ->where('"gameId" = ?', $this->_gameId)
            ->group('winnerId');

        $array = array();

        foreach ($this->selectAll($select) as $v) {
            $array[$playersInGameColors[$v['winnerId']]] = $v['count'];
        }

        return $array;
    }

    public function countLost($playersInGameColors)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('count(*)', 'loserId'))
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"loserId" != 0')
            ->group('loserId');

        $array = array();

        foreach ($this->selectAll($select) as $v) {
            $array[$playersInGameColors[$v['loserId']]] = $v['count'];
        }

        return $array;
    }

    public function add($castleId, $winnerId, $loserId)
    {
        $data = array(
            'castleId' => $castleId,
            'gameId' => $this->_gameId,
            'winnerId' => $winnerId,
            'loserId' => $loserId
        );

        $this->insert($data);
    }
}

