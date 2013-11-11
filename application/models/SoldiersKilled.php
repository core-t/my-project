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

    public function add($soldierId, $winnerId, $loserId)
    {
        $data = array(
            'soldierId' => $soldierId,
            'gameId' => $this->_gameId,
            'winnerId' => $winnerId,
            'loserId' => $loserId
        );

        $this->insert($data);
    }
}

