<?php

class Application_Model_PlayersInGame extends Game_Db_Table_Abstract
{
    protected $_name = 'playersingame';
//    protected $_primary = 'mapPlayerId';
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
            ->from(array('a' => $this->_name), array('color', 'playerId'))
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', array('computer'))
            ->where('a."gameId" = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' IS NOT NULL');

        return $this->selectAll($select);
    }

    public function getAllColors()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('color', 'playerId'))
            ->join(array('b' => 'mapplayers'), 'a . "mapPlayerId" = b . "mapPlayerId"')
            ->where('a."gameId" = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' IS NOT NULL');

        $colors = array();

        foreach ($this->selectAll($select) as $row) {
            $colors[$row['playerId']] = $row['color'];
        }

        return $colors;
    }

    public function getPlayersWaitingForGame()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('mapPlayerId', 'playerId'))
            ->join(array('b' => 'player'), 'a . "playerId" = b . "playerId"', array('firstName', 'lastName', 'computer'))
            ->join(array('c' => 'mapplayers'), 'a . "mapPlayerId" = c . "mapPlayerId"', array('color' => 'shortName'))
            ->where('a."gameId" = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('webSocketServerUserId') . ' IS NOT NULL OR computer = true');

        return $this->selectAll($select);
    }

    public function getPlayerIdByColor($color)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'playerId')
            ->join(array('b' => 'mapplayers'), 'a . "mapPlayerId" = b . "mapPlayerId"')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('shortName') . ' = ?', $color);

        return $this->selectOne($select);
    }

    public function getColorByPlayerId($playerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), null)
            ->join(array('b' => 'mapplayers'), 'a . "mapPlayerId" = b . "mapPlayerId"', array('color' => 'shortName'))
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);

        return $this->selectOne($select);
    }

    public function disconnectNotActive()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'playerId')
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
            ->where('"gameId" = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('webSocketServerUserId') . ' IS NULL')
            ->where('computer = false');

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' IN (?)', new Zend_Db_Expr($select->__toString())),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
        );

        $this->delete($where);
    }

    public function isNoComputerColorInGame($color)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'min(b."playerId")')
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
            ->join(array('c' => 'mapplayers'), 'a . "mapPlayerId" = c . "mapPlayerId"', null)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('shortName') . ' = ?', $color)
            ->where('"webSocketServerUserId" IS NOT NULL'); // human player

        return $this->selectOne($select);
    }

    public function isColorInGame($color)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'min(b."playerId")')
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
            ->join(array('c' => 'mapplayers'), 'a . "mapPlayerId" = c . "mapPlayerId"', null)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('shortName') . ' = ?', $color)
            ->where('"webSocketServerUserId" IS NOT NULL OR computer = true');

        return $this->selectOne($select);
    }

    public function updatePlayerReady($playerId, $color)
    {
        $mapPlayerIdToShortNameRelations = Zend_Registry::get('mapPlayerIdToShortNameRelations');

        if ($color && $this->getColorByPlayerId($playerId) == $color) {
            $data['mapPlayerId'] = null;
        } else {
            $data['mapPlayerId'] = $mapPlayerIdToShortNameRelations[$color];
        }

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
        );

        $this->update($data, $where);
    }
}

