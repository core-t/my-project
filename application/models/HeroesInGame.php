<?php

class Application_Model_HeroesInGame extends Coret_Db_Table_Abstract
{
    protected $_name = 'heroesingame';
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

    public function add($armyId, $heroId)
    {
        $data = array(
            'heroId' => $heroId,
            'armyId' => $armyId,
            'gameId' => $this->_gameId,
            'movesLeft' => 16
        );

        return $this->insert($data);
    }

    public function resetMovesLeft($playerId)
    {
    }

    public function getArmyHeroes($armyId)
    {
        $select = $this->_db->select()
            ->from(array('a' => 'hero'), array('heroId', 'numberOfMoves', 'attackPoints', 'defensePoints', 'name'))
            ->join(array('b' => $this->_name), 'a."heroId" = b."heroId"', array('movesLeft'))
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('armyId') . ' = ?', $armyId)
            ->order('attackPoints DESC', 'defensePoints DESC', 'numberOfMoves DESC');

        return $this->selectAll($select);
    }

    public function updateMovesLeft($movesLeft, $heroId)
    {
        $data = array(
            'movesLeft' => $movesLeft
        );

        $where = array(
            $this->_db->quoteInto('"heroId" = ?', $heroId),
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId)
        );

        $this->update($data, $where);
    }

    public function getDeadHeroId($playerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'armyId')
            ->join(array('b' => 'hero'), 'a."heroId" = b."heroId"', 'heroId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId);

        $result = $this->selectRow($select);
        if (!isset($result['armyId'])) {
            return $result['heroId'];
        }
    }

    public function armyRemoveHero($heroId)
    {
        $data = array(
            'armyId' => null
        );

        $where = array(
            $this->_db->quoteInto('"heroId" = ?', $heroId),
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
        );

        return $this->update($data, $where);
    }
}

