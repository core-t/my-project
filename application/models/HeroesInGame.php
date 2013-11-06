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

    public function addToArmy($armyId, $heroId, $movesLeft)
    {

        $data = array(
            'armyId' => $armyId,
            'movesLeft' => $movesLeft
        );
        $where = array(
            $this->_db->quoteInto('"heroId" = ?', $heroId),
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId)
        );

        return $this->update($data, $where);
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
            $this->_db->quoteInto($this->_db->quoteIdentifier('heroId') . ' = ?', $heroId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
        );

        $this->update($data, $where);
    }

    public function getDeadHeroId($playerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'armyId')
            ->join(array('b' => 'hero'), 'a."heroId" = b."heroId"', 'heroId')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
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
            $this->_db->quoteInto($this->_db->quoteIdentifier('heroId') . ' = ?', $heroId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
        );

        return $this->update($data, $where);
    }

    public function heroesUpdateArmyId($oldArmyId, $newArmyId)
    {
        $data = array(
            'armyId' => $newArmyId
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('armyId') . ' = ?', $oldArmyId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
        );

        return $this->update($data, $where);
    }

    public function resetHeroesMovesLeft($playerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('movesLeft', 'heroId'))
            ->join(array('b' => 'hero'), 'a."heroId"=b."heroId"', '')
            ->where('"playerId" = ?', $playerId)
            ->where('a."gameId" = ?', $this->_gameId);

        foreach ($this->selectAll($select) as $hero) {
            if ($hero['movesLeft'] > 2) {
                $hero['movesLeft'] = 2;
            }

            $select = $this->_db->select()
                ->from('hero', new Zend_Db_Expr('"numberOfMoves" + ' . $hero['movesLeft']))
                ->where('"playerId" = ?', $playerId)
                ->where('"heroId" = ?', $hero['heroId']);

            $data = array(
                'movesLeft' => new Zend_Db_Expr('(' . $select->__toString() . ')')
            );

            $where = array(
                $this->_db->quoteInto('"heroId" = ?', $hero['heroId']),
                $this->_db->quoteInto('"gameId" = ?', $this->_gameId)
            );

            $this->update($data, $where);
        }
    }

    public function heroUpdateArmyId($heroId, $newArmyId)
    {
        $data = array(
            'armyId' => $newArmyId
        );
        $where = array(
            $this->_db->quoteInto('"heroId" = ?', $heroId),
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId)
        );

        return $this->update($data, $where);
    }

    public function zeroHeroMovesLeft($armyId, $heroId, $playerId)
    {
        $data = array(
            'movesLeft' => 0
        );

        $where = array(
            $this->_db->quoteInto('"armyId" = ?', $armyId),
            $this->_db->quoteInto('"heroId" = ?', $heroId),
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId)
        );

        return $this->update($data, $where);
    }

    public function isThisCorrectHero($playerId, $heroId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'heroId')
            ->join(array('b' => 'hero'), 'a."heroId" = b."heroId"')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('a."heroId" = ?', $heroId);

        return $this->selectOne($select);
    }

    public function getHeroIdByArmyIdPlayerId($armyId, $playerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'heroId')
            ->join(array('b' => 'hero'), 'a."heroId" = b."heroId"')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('"armyId" = ?', $armyId);

        return $this->selectOne($select);
    }
}

