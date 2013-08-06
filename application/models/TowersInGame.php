<?php

class Application_Model_TowersInGame extends Game_Db_Table_Abstract
{
    protected $_name = 'towersingame';
    protected $_primary = 'towerId';
    protected $_db;

    public function __construct($gameId, $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getTowers()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), $this->_primary)
            ->join(array('b' => 'playersingame'), 'a."playerId" = b."playerId" AND a."gameId" = b."gameId"', 'color')
            ->where('a."gameId" = ?', $this->_gameId);
        try {
            $result = $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
        $towers = array();
        foreach ($result as $k => $row) {
            $towers[$row['towerId']] = $row['color'];
        }
        return $towers;
    }

    public function getTower($towerId)
    {
        try {
            $select = $this->_db->select()
                ->from(array('a' => $this->_name), $this->_primary)
                ->join(array('b' => 'playersingame'), 'a."playerId" = b."playerId" AND a."gameId" = b."gameId"', 'color')
                ->where('"' . $this->_primary . '" = ?', $towerId)
                ->where('a."gameId" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            return $result[0];
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function calculateIncomeFromTowers($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'towerId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" IN (?)', $playerId);
        try {
            $towers = $this->_db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());

            return;
        }

        return count($towers) * 5;
    }

    public function towerExists($towerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'towerId')
            ->where('"towerId" = ?', $towerId)
            ->where('"gameId" = ?', $this->_gameId);
        try {
            return $this->_db->fetchOne($select);
        } catch (Exception $e) {
            echo $e;
            echo $select->__toString();
        }
    }

    public function changeTowerOwner($towerId, $playerId)
    {
        $data = array(
            'playerId' => $playerId
        );
        $where = array(
            $this->_db->quoteInto('"towerId" = ?', $towerId),
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId)
        );

        return self::update($this->_name, $data, $where, $db, true);
    }

    public function addTower($towerId, $playerId)
    {
        $data = array(
            'towerId' => $towerId,
            'gameId' => $this->_gameId,
            'playerId' => $playerId
        );
        try {
            return $this->_db->insert($this->_name, $data);
        } catch (Exception $e) {
            echo($e);
        }
    }
}

