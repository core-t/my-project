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
            ->from(array('a' => $this->_name), array('mapPlayerId', 'playerId'))
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', array('computer'))
            ->where('a."gameId" = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' IS NOT NULL');

        return $this->selectAll($select);
    }

    public function getAllColors()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'playerId')
            ->join(array('b' => 'mapplayers'), 'a . "mapPlayerId" = b . "mapPlayerId"', array('color' => 'shortName'))
            ->where('a."gameId" = ?', $this->_gameId)
            ->where('a . ' . $this->_db->quoteIdentifier('mapPlayerId') . ' IS NOT NULL');

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
//            ->join(array('c' => 'mapplayers'), 'a . "mapPlayerId" = c . "mapPlayerId"', array('color' => 'shortName'))
            ->where('a."gameId" = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('webSocketServerUserId') . ' IS NOT NULL OR computer = true');

        return $this->selectAll($select);
    }

    public function getPlayerIdByMapPlayerId($mapPlayerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'playerId')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' = ?', $mapPlayerId);

        return $this->selectOne($select);
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

    public function getMapPlayerIdByPlayerId($playerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'mapPlayerId')
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

    public function disconnectFromGame($playerId)
    {
        $where = array(
            $this->_db->quoteInto('"playerId" = ?', $playerId),
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId)
        );

        $this->delete($where);
    }

    public function joinGame($playerId)
    {
        $data = array(
            'gameId' => $this->_gameId,
            'playerId' => $playerId,
            'accessKey' => $this->generateKey()
        );

        $this->insert($data);
    }

    private function generateKey()
    {
        return md5(rand(0, time()));
    }

    public function isNoComputerColorInGame($mapPlayerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'min(b."playerId")')
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' = ?', $mapPlayerId)
            ->where('"webSocketServerUserId" IS NOT NULL'); // human player

        return $this->selectOne($select);
    }

    public function isColorInGame($mapPlayerId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'min(b."playerId")')
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' = ?', $mapPlayerId)
            ->where('"webSocketServerUserId" IS NOT NULL OR computer = true');

        return $this->selectOne($select);
    }

    public function getPlayersInGameReady()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name))
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', array('computer'))
            ->join(array('c' => 'mapplayers'), 'a . "mapPlayerId" = c . "mapPlayerId"', array('color' => 'shortName'))
            ->where('a . ' . $this->_db->quoteIdentifier('mapPlayerId') . ' IS NOT NULL')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId);

        return $this->selectAll($select);
    }

    public function updatePlayerReady($playerId, $mapPlayerId)
    {
        if ($mapPlayerId && $this->getMapPlayerIdByPlayerId($playerId) == $mapPlayerId) {
            $data['mapPlayerId'] = null;
        } else {
            $data['mapPlayerId'] = $mapPlayerId;
        }

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
        );

        $this->update($data, $where);
    }

    public function findNewGameMaster()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'playerId')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('webSocketServerUserId') . ' IS NOT NULL');

        return $this->selectOne($select);
    }

    public function isPlayerInGame($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'gameId')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);

        return $this->selectOne($select);
    }

    public function getInGameWSSUIds()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'webSocketServerUserId')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId);

        return $this->selectAll($select);
    }

    public function getComputerPlayerId()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'min(b."playerId")')
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
            ->where($this->_db->quoteIdentifier('gameId') . ' != ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' IS NOT NULL')
            ->where('computer = true');

        $ids = $this->getComputerPlayersIds();
        if ($ids) {
            $select->where('a."playerId" NOT IN (?)', new Zend_Db_Expr($ids));
        }

        return $this->selectOne($select);
    }

    private function getComputerPlayersIds()
    {
        $ids = '';
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), 'playerId')
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' IS NOT NULL')
            ->where('computer = true');

        foreach ($this->selectAll($select) as $row) {
            if ($ids) {
                $ids .= ',';
            }
            $ids .= $row['playerId'];
        }

        return $ids;

    }

    public function getPlayerInGameGold($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'gold')
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId);

        return $this->selectOne($select);
    }

    public function updatePlayerInGameGold($playerId, $gold)
    {
        $data['gold'] = $gold;
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
        );

        return $this->update($data, $where);
    }

    public function playerTurnActive($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'turnActive')
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
            ->where($this->_db->quoteIdentifier('turnActive') . ' = ?', true)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId);

        return $this->selectOne($select);
    }

    public function turnActivate($playerId)
    {
        $data = array(
            'turnActive' => 'true'
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
        );

        $this->update($data, $where);

        $data['turnActive'] = 'false';

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('turnActive') . ' = ?', true),
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' != ?', $playerId)
        );

        $this->update($data, $where);
    }

    public function setPlayerLostGame($playerId)
    {
        $data['lost'] = 'true';

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
        );

        $this->update($data, $where);
    }

    public function playerLost($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'lost')
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
            ->where('lost = ?', true)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId);

        return $this->selectOne($select);
    }

    public function checkAccessKey($playerId, $accessKey)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'playerId')
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('accessKey') . ' = ?', $accessKey);

        return $this->selectOne($select);
    }

    public function updatePlayerInGameWSSUId($playerId, $wssuid)
    {

        $data = array(
            'webSocketServerUserId' => $wssuid
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
        );

        $this->update($data, $where);
    }

    public function isPlayerReady($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'mapPlayerId')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' IS NOT NULL');

        return $this->selectOne($select);
    }

//    public function getPlayerColor($playerId)
//    {
//        $select = $this->_db->select()
//            ->from($this->_name, 'mapPlayerId')
//            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
//            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);
//
//        return $this->selectOne($select);
//    }

    public function getNumberOfPlayers()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'count(*) as number')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' IS NOT NULL');

        return $this->_db->fetchOne($select);
    }

    public function getSelectForMyGames($playerId)
    {
        return $this->_db->select()
            ->from($this->_name, 'gameId')
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' is not null')
            ->where('lost = false')
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId);
    }

    public function getGamePlayers()
    {
        $players = array();

        $select = $this->_db->select()
            ->from(array('b' => $this->_name), 'playerId')
            ->join(array('a' => 'player'), 'a."playerId" = b."playerId"', array('firstName', 'lastName'))
            ->join(array('c' => 'mapplayers'), 'b . "mapPlayerId" = c . "mapPlayerId"', array('color' => 'shortName', 'longName', 'backgroundColor', 'textColor'))
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where('b . ' . $this->_db->quoteIdentifier('mapPlayerId') . ' is not null');
        foreach ($this->selectAll($select) as $v) {
            $players[$v['playerId']] = $v;
        }
        return $players;
    }
}

