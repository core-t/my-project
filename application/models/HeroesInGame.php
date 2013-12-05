<?php

class Application_Model_HeroesInGame extends Coret_Db_Table_Abstract
{
    protected $_name = 'heroesingame';
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

    public function getForBattle($ids)
    {
        $select = $this->_db->select()
            ->from(array('a' => 'hero'), array('attackPoints', 'defensePoints', 'name'))
            ->join(array('b' => $this->_name), 'a."heroId" = b."heroId"', 'heroId')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('armyId') . ' IN (?)', $ids)
            ->order('attackPoints DESC', 'defensePoints DESC', 'numberOfMoves DESC');

        return $this->selectAll($select);

//        foreach ($result as $k => $row) {
//            $mInventory = new Application_Model_Inventory($row['heroId'], $gameId, $db);
//            $result[$k]['artefacts'] = $mInventory->getAll();
//            $result[$k]['artefacts'] = self::getArtefactsByHeroId($gameId, $row['heroId'], $db);
//        }

//        return $result;
    }

    public function getArmyHeroes($armyId)
    {
        $select = $this->_db->select()
            ->from(array('a' => 'hero'), array('numberOfMoves', 'attackPoints', 'defensePoints', 'name'))
            ->join(array('b' => $this->_name), 'a."heroId" = b."heroId"', array('heroId', 'movesLeft'))
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

    public function resetMovesLeftForAll($playerId)
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

    public function isHeroInArmy($armyId, $playerId, $heroId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'heroId')
            ->join(array('b' => 'hero'), 'a."heroId" = b."heroId"')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('"armyId" = ?', $armyId)
            ->where('a."heroId" = ?', $heroId);

        return $this->selectOne($select);
    }

    public function isHeroInGame($heroId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'heroId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"heroId" = ?', $heroId);

        return $this->selectOne($select);
    }

    public function connectHero($heroId)
    {
        $data = array(
            'armyId' => null,
            'gameId' => $this->_gameId,
            'heroId' => $heroId
        );

        return $this->insert($data);
    }

    public function getDeadHeroId($playerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'armyId')
            ->join(array('b' => 'hero'), 'a."heroId" = b."heroId"', 'heroId')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId);

        $result = $this->selectRow($select);

        if (!$result['armyId']) {
            return $result['heroId'];
        }

        $mArmy = new Application_Model_Army($this->_gameId, $this->_db);

        if (!$mArmy->getArmyPositionByArmyIdPlayerId($result['armyId'], $playerId)) {
            return $result['heroId'];
        }
    }


}

