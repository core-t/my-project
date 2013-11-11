<?php

class Application_Model_HeroesKilled extends Coret_Db_Table_Abstract
{
    protected $_name = 'heroeskilled';
    protected $_primary = 'heroeskilledId';
    protected $_sequence = 'heroeskilled_heroeskilledId_seq';
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

    public function add($heroId, $winnerId, $loserId)
    {
        $data = array(
            'heroId' => $heroId,
            'gameId' => $this->_gameId,
            'winnerId' => $winnerId,
            'loserId' => $loserId
        );

        $this->insert($data);
    }
}

