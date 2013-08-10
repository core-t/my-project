<?php

class Application_Model_CastlesInGame extends Game_Db_Table_Abstract
{
    protected $_name = 'castlesingame';
    protected $_primary = array('castleId', 'gameId');
    protected $_sequence = '';
    protected $_castleId;
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

    public function setProduction($castleId, $playerId, $unitId)
    {
        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"castleId" = ?', $castleId),
            $this->_db->quoteInto('"playerId" = ?', $playerId)
        );

        $data = array(
            'production' => $unitId,
            'productionTurn' => 0
        );

        return $this->update($data, $where);
    }

    public function getProduction($castleId, $playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('production', 'productionTurn'))
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"castleId" = ?', $castleId)
            ->where('"playerId" = ?', $playerId);

        return $this->selectRow($select);
    }
}

