<?php

class Application_Model_CastlesDestroyed extends Coret_Db_Table_Abstract
{
    protected $_name = 'castlesdestroyed';
    protected $_primary = 'castlesdestroyedId';
    protected $_sequence = 'castlesdestroyed_castlesdestroyedId_seq';
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

    public function countAll($playersInGameColors)
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

