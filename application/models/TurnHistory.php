<?php

class Application_Model_TurnHistory extends Coret_Db_Table_Abstract
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
            ->from(array('a' => $this->_name), array('number', 'date'))
            ->join(array('b' => 'playersingame'), 'a."playerId" = b."playerId"', null)
            ->join(array('c' => 'mapplayers'), 'b."mapPlayerId" = c."mapPlayerId"', array('shortName'))
            ->where('a."gameId" = ?', $this->_gameId)
            ->where('b."gameId" = ?', $this->_gameId)
            ->order($this->_primary);
        return $this->selectAll($select);
    }

    public function add($playerId, $number)
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
