<?php

class Application_Model_Turn extends Coret_Db_Table_Abstract
{

    protected $_name = 'turn';
    protected $_primary = 'turnId';

    public function __construct($gameId, $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getTurnHistory()
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where('"gameId" = ?', $this->_gameId)
            ->order($this->_primary);
        return $this->selectAll($select);
    }

    public function insertTurn($playerId, $number)
    {
        $data = array(
            'number' => $number,
            'date' => new Zend_Db_Expr('now()'),
            'playerId' => $playerId,
            'gameId' => $this->_gameId
        );
        $this->insert($data);
    }
}
