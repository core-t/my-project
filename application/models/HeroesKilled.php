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

    public function countKilled($playersInGameColors)
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
            ->group('loserId');

        $array = array();

        foreach ($this->selectAll($select) as $v) {
            $array[$playersInGameColors[$v['loserId']]] = $v['count'];
        }

        return $array;
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

