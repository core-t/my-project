<?php

class Application_Model_CastlesDestroyed extends Coret_Db_Table_Abstract
{
    protected $_name = '';
    protected $_primary = '';
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

    public function getAll()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('playerId', 'mapCastleId'))
            ->where('"gameId" = ?', $this->_gameId)
            ->order($this->_primary);
        return $this->selectAll($select);
    }

    public function countAll()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'count(*)')
            ->where('"gameId" = ?', $this->_gameId)
            ->group('playerId');
        return $this->selectAll($select);
    }

    public function add($castleId, $playerId)
    {
        $data = array(
            'mapCastleId' => $castleId,
            'gameId' => $this->_gameId,
            'playerId' => $playerId
        );

        $this->insert($data);
    }
}

