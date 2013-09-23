<?php

class Application_Model_HeroesInGame extends Game_Db_Table_Abstract
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

        $result = $this->selectAll($select);

        foreach ($result as $k => $row) {
            $mInventory = new Application_Model_Inventory($row['heroId'], $this->_gameId);
            $result[$k]['artifacts'] = $mInventory->getAll();
        }

        return $result;
    }

}

