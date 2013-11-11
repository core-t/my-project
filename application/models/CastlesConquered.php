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
            ->from($this->_name, array('winnerId', 'loserId', 'mapCastleId'))
            ->where('"gameId" = ?', $this->_gameId)
            ->order($this->_primary);
        return $this->selectAll($select);
    }

    public function countConquered()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'count(*)')
            ->where('"gameId" = ?', $this->_gameId)
            ->group('winnerId');
        return $this->selectAll($select);
    }

    public function countLost()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'count(*)')
            ->where('"gameId" = ?', $this->_gameId)
            ->group('loserId');
        return $this->selectAll($select);
    }

    public function add($castleId, $winnerId, $loserId)
    {
        $data = array(
            'mapCastleId' => $castleId,
            'gameId' => $this->_gameId,
            'winnerId' => $winnerId,
            'loserId' => $loserId
        );

        $this->insert($data);
    }
}

