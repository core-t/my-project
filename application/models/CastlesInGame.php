<?php

class Application_Model_CastlesInGame extends Coret_Db_Table_Abstract
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

    public function cancelProductionRelocation($playerId, $castleId)
    {
        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"castleId" = ?', $castleId),
            $this->_db->quoteInto('"playerId" = ?', $playerId)
        );

        $data = array(
            'relocationCastleId' => null
        );

        return $this->update($data, $where);
    }

    public function setProduction($playerId, $castleId, $unitId, $relocationCastleId = null)
    {
        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"castleId" = ?', $castleId),
            $this->_db->quoteInto('"playerId" = ?', $playerId)
        );

        $data = array(
            'productionId' => $unitId,
            'productionTurn' => 0,
            'relocationCastleId' => $relocationCastleId
        );

        return $this->update($data, $where);
    }

    public function getProduction($castleId, $playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('productionId', 'productionTurn', 'relocationCastleId'))
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"castleId" = ?', $castleId)
            ->where('"playerId" = ?', $playerId);

        return $this->selectRow($select);
    }

    public function razeCastle($castleId, $playerId)
    {
        $mCastlesDestroyed = new Application_Model_CastlesDestroyed($this->_gameId, $this->_db);
        $mCastlesDestroyed->add($castleId, $playerId);

        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"castleId" = ?', $castleId),
            $this->_db->quoteInto('"playerId" = ?', $playerId)
        );

        $data = array(
            'razed' => 'true',
            'productionId' => null,
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

        foreach ($this->selectAll($select) as $val) {
            $castles[$val['castleId']] = $val;
        }

        return $castles;
    }

    public function getPlayerCastles($playerId)
    {
        $playersCastles = array();

        $select = $this->_db->select()
            ->from($this->_name, array('productionId', 'productionTurn', 'defenseMod', 'castleId', 'relocationCastleId'))
            ->where('"playerId" = ?', $playerId)
            ->where('"gameId" = ?', $this->_gameId)
            ->where('razed = false');

        foreach ($this->selectAll($select) as $val) {
            $playersCastles[$val['castleId']] = $val;
        }

        return $playersCastles;
    }

    public function buildDefense($castleId, $playerId, $defenseMod)
    {
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

    public function changeOwner($castle, $playerId)
    {
        $defenseMod = $this->getCastleDefenseModifier($castle['castleId']);
        $defense = $castle['defense'] + $defenseMod;

        if ($defense > 1) {
            $defenseMod--;
        }

        $select = $this->_db->select()
            ->from($this->_name, 'playerId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"castleId" = ?', $castle['castleId']);

        $mCastlesConquered = new Application_Model_CastlesConquered($this->_gameId, $this->_db);
        $mCastlesConquered->add($castle['castleId'], $playerId, new Zend_Db_Expr('(' . $select->__toString() . ')'));

        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"castleId" = ?', $castle['castleId'])
        );

        $data = array(
            'defenseMod' => $defenseMod,
            'playerId' => $playerId,
            'productionId' => null,
            'productionTurn' => 0,
        );

        $this->update($data, $where);
    }

    public function getCastleDefenseModifier($castleId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'defenseMod')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"castleId" = ?', $castleId);

        return $this->selectOne($select);
    }

    public function addCastle($castleId, $playerId)
    {
        $mCastlesConquered = new Application_Model_CastlesConquered($this->_gameId, $this->_db);
        $mCastlesConquered->add($castleId, $playerId, 0);

        $data = array(
            'castleId' => $castleId,
            'playerId' => $playerId,
            'gameId' => $this->_gameId
        );

        $this->insert($data);
    }

    public function resetProductionTurn($castleId, $playerId)
    {

        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"playerId" = ?', $playerId),
            $this->_db->quoteInto('"castleId" = ?', $castleId)
        );
        $data = array(
            'productionTurn' => 0
        );

        return $this->update($data, $where);
    }

    public function getAllCastles()
    {
        $castles = array();

        $select = $this->_db->select()
            ->from($this->_name)
            ->where('"gameId" = ?', $this->_gameId);
        foreach ($this->_db->query($select)->fetchAll() as $val) {
            $castles[$val['castleId']] = $val;
        }

        return $castles;
    }

    public function increaseAllCastlesProductionTurn($playerId)
    {
        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"playerId" = ?', $playerId)
        );
        $data = array(
            'productionTurn' => new Zend_Db_Expr('"productionTurn" + 1')
        );

        return $this->update($data, $where);
    }

    public function getColorByCastleId($castleId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'playerId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"castleId" = ?', $castleId);

        $playerId = $this->selectOne($select);

        if ($playerId) {
            $mPlayersInGame = new Application_Model_PlayersInGame($this->_gameId, $this->_db);
            return $mPlayersInGame->getColorByPlayerId($playerId);
        } else {
            print_r(debug_backtrace(0, 2));
        }
    }

    public function getPlayerIdByCastleId($castleId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'playerId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"castleId" = ?', $castleId);

        return $this->selectOne($select);
    }

    public function playerCastlesExists($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_primary)
            ->where('"playerId" = ?', $playerId)
            ->where('"gameId" = ?', $this->_gameId)
            ->where('razed = false');

        $result = $this->selectAll($select);

        if (count($result)) {
            return true;
        }
    }

    public function isPlayerCastle($castleId, $playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'castleId')
            ->where('razed = false')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('"castleId" = ?', $castleId);

        return $this->selectOne($select);
    }

    public function isEnemyCastle($castleId, $playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'castleId')
            ->where('razed = false')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" != ?', $playerId)
            ->where('"castleId" = ?', $castleId);

        return $this->selectOne($select);
    }

    public function enemiesCastlesExist($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'castleId')
            ->where('"playerId" != ?', $playerId)
            ->where('"gameId" = ?', $this->_gameId)
            ->where('razed = false');

        $result = $this->selectAll($select);
        if (count($result)) {
            return true;
        }
    }

}

