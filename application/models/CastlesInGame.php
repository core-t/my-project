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

    public function razeCastle($castleId, $playerId)
    {
        $data = array(
            'mapCastleId' => $castleId,
            'gameId' => $this->_gameId,
            'playerId' => $playerId
        );

        try {
            $this->_db->insert('castlesdestoyed', $data);
        } catch (Exception $e) {
            echo($e);

            return;
        }

        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"castleId" = ?', $castleId),
            $this->_db->quoteInto('"playerId" = ?', $playerId)
        );

        $data = array(
            'razed' => 'true',
            'production' => null,
            'productionTurn' => 0,
        );

        return $this->update($data, $where);
    }

    public function getRazedCastles()
    {
        $castles = array();

        $select = $this->_db->select()
            ->from($this->_name)
            ->where('"gameId" = ?', $this->_gameId)
            ->where('razed = true');

        foreach ($this->selectAll($select) as $key => $val) {
            $castles[$val['castleId']] = $val;
        }

        return $castles;
    }

    public function getPlayerCastles($playerId)
    {
        $playersCastles = array();

        $select = $this->_db->select()
            ->from($this->_name, array('production', 'productionTurn', 'defenseMod', 'castleId'))
            ->where('"playerId" = ?', $playerId)
            ->where('"gameId" = ?', $this->_gameId)
            ->where('razed = false');

        foreach ($this->selectAll($select) as $val) {
            $playersCastles[$val['castleId']] = $val;
            unset($playersCastles[$val['castleId']]['castleId']);
        }

        return $playersCastles;
    }

    public function buildDefense($castleId, $playerId, $defenseMod)
    {
//        $select = $db->select()
//            ->from('castlesingame', 'defenseMod')
//            ->where('"gameId" = ?', $gameId)
//            ->where('"playerId" = ?', $playerId)
//            ->where('"castleId" = ?', $castleId);
//        $defenseMod = $db->fetchOne($select);
//        $defensePoints = Application_Model_Board::getCastleDefense($castleId);
//
//        if ($defensePoints + $defenseMod < 1) {
//            $defenseMod = 1 - $defensePoints;
//        }
//        $defenseMod++;

        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"playerId" = ?', $playerId),
            $this->_db->quoteInto('"castleId" = ?', $castleId)
        );
        $data = array(
            'defenseMod' => $defenseMod
        );

        return $this->update($data, $where);
    }


}

