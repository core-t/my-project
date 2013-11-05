<?php

class Application_Model_Game extends Coret_Db_Table_Abstract
{

    protected $_name = 'game';
    protected $_primary = 'gameId';
    protected $_sequence = "game_gameId_seq";
    protected $_gameId;

    public function __construct($gameId = 0, $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    private function generateKey()
    {
        return md5(rand(0, time()));
    }

    public function createGame($numberOfPlayers, $playerId, $mapId)
    {
        $data = array(
            'numberOfPlayers' => $numberOfPlayers,
            'gameMasterId' => $playerId,
            'mapId' => $mapId
        );

        $this->_db->insert($this->_name, $data);
        $this->_gameId = $this->_db->lastSequenceId($this->_db->quoteIdentifier($this->_sequence));
        return $this->_gameId;
    }

    public function isActive()
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_primary)
            ->where('"isActive" = true')
            ->where('"' . $this->_primary . '" = ?', $this->_gameId);
        $result = $this->_db->query($select)->fetchAll();
        if (!empty($result[0][$this->_primary]))
            return true;
    }

    public function getOpen()
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->join('map', $this->_name . '.' . $this->_db->quoteIdentifier('mapId') . '=map.' . $this->_db->quoteIdentifier('mapId'), 'name')
            ->where('"isOpen" = true')
            ->order('begin DESC');
        $result = $this->_db->query($select)->fetchAll();

        foreach ($result as $k => $game) {
            $select = $this->_db->select()
                ->from('playersingame', 'count(*)')
                ->where('"gameId" = ?', $game['gameId'])
                ->where('"webSocketServerUserId" IS NOT NULL');
            $playersInGame = $this->_db->query($select)->fetchAll();
            if ($playersInGame[0]['count'] > 0) {
                $result[$k]['playersingame'] = $playersInGame[0]['count'];
                $select = $this->_db->select()
                    ->from('player', array('firstName', 'lastName'))
                    ->where('"playerId" = ?', $result[$k]['gameMasterId']);
                $gameMaster = $this->_db->query($select)->fetchAll();
                $result[$k]['gameMaster'] = $gameMaster[0]['firstName'] . ' ' . $gameMaster[0]['lastName'];
            } else {
                unset($result[$k]);
            }
        }
        return $result;
    }

    public function getMyGames($playerId, $pageNumber)
    {
        $mPlayersInGame = new Application_Model_PlayersInGame($this->_gameId);
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('gameMasterId', 'turnNumber', $this->_primary, 'numberOfPlayers', 'begin', 'turnPlayerId'))
            ->join(array('b' => 'playersingame'), 'a."gameId" = b."gameId"', null)
            ->join(array('c' => 'mapplayers'), 'b . "mapPlayerId" = c . "mapPlayerId"', null)
            ->where('"isOpen" = false')
            ->where('"isActive" = true')
            ->where('a."gameId" IN ?', $mPlayersInGame->getSelectForMyGames($playerId))
            ->where('b."playerId" = ?', $playerId)
            ->order('begin DESC');
        try {
            $paginator = new Zend_Paginator(new Zend_Paginator_Adapter_DbSelect($select));
            $paginator->setCurrentPageNumber($pageNumber);
            $paginator->setItemCountPerPage(10);
        } catch (Exception $e) {
            $l = new Coret_Model_Logger('www');
            $l->log($select->__toString());
            $l->log($e);
            throw $e;
        }

        $mPlayer = new Application_Model_Player();

        foreach ($paginator as &$val) {
            $mPlayersInGame = new Application_Model_PlayersInGame($val['gameId']);
            $val['players'] = $mPlayersInGame->getGamePlayers();
            $val['playerTurn'] = $mPlayer->getPlayer($val['turnPlayerId']);
        }

        return $paginator;
    }

    public function playerIsAlive($playerId)
    {
        try {
            $select = $this->_db->select()
                ->from('playersingame', 'playerId')
                ->where('lost = false')
                ->where('"playerId" = ?', $playerId)
                ->where('"' . $this->_primary . '" = ?', $this->_gameId);
            if ($this->_db->fetchOne($select)) {
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function startGame($turnPlayerId)
    {
        $data = array(
            'turnPlayerId' => $turnPlayerId,
            'isOpen' => 'false'
        );

        $this->updateGame($data);
    }

    public function setNewGameMaster($gameMasterId)
    {
        if ($gameMasterId) {
            $data = array(
                'gameMasterId' => $gameMasterId
            );
            $this->updateGame($data);
        }
    }

    public function getGame()
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where('"' . $this->_primary . '" = ?', $this->_gameId);
        return $this->_db->fetchRow($select);
    }

    public function getPlayerIdByColor($color)
    {
        $select = $this->_db->select()
            ->from('playersingame', 'playerId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('color = ?', $color);
        try {
            return $this->_db->fetchOne($select);
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }
    }

    public function isPlayerInGame($playerId)
    {
        try {
            $select = $this->_db->select()
                ->from('playersingame', 'gameId')
                ->where('"gameId" = ?', $this->_gameId)
                ->where('"playerId" = ?', $playerId);
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0]['gameId'])) {
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function joinGame($playerId)
    {
        $data = array(
            'gameId' => $this->_gameId,
            'playerId' => $playerId,
            'accessKey' => $this->generateKey()
        );
        $this->_db->insert('playersingame', $data);
    }

    public function disconnectFromGame($gameId, $playerId)
    {
        if (empty($gameId)) {
            $gameId = $this->_gameId;
        }
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $this->_db->delete('playersingame', $where);
    }

    public function disconnectNotActive()
    {
        $select = $this->_db->select()
            ->from('playersingame', 'playerId')
            ->where('"' . $this->_primary . '" = ?', $this->_gameId)
            ->where('"webSocketServerUserId" IS NULL');
        $where = array(
            $this->_db->quoteInto('"playerId" IN (?)', new Zend_Db_Expr($select->__toString()))
        );
        $this->_db->delete('playersingame', $where);
    }

    public function getGameMasterId()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'gameMasterId')
            ->where('"' . $this->_primary . '" = ?', $this->_gameId);
        return $this->selectOne($select);
    }

    public function updateGameMaster($playerId)
    {
        $select = $this->_db->select()
            ->from('playersingame', 'gameId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" = ?', $this->getGameMasterId())
            ->where('"webSocketServerUserId" IS NOT NULL');
        if (!$this->_db->fetchOne($select)) {
            $data = array(
                'gameMasterId' => $playerId
            );
            $this->updateGame($data);
        }
    }

    public function isGameMaster($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('gameMasterId'))
            ->where('"' . $this->_primary . '" = ?', $this->_gameId)
            ->where('"gameMasterId" = ?', $playerId);

        return $this->selectOne($select);
    }

    public function isGameStarted()
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_primary)
            ->where('"isOpen" = false')
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_gameId);

        return $this->selectOne($select);
    }

    public function updateGame($data)
    {
        $where = $this->_db->quoteInto($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_gameId);
        return $this->update($data, $where);
    }

    public function getComputerPlayerId()
    {
        $select = $this->_db->select()
            ->from(array('a' => 'playersingame'), 'min(b."playerId")')
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
            ->where('a."gameId" != ?', $this->_gameId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' is not null')
            ->where('computer = true'); //throw new Exception($select->__toString());
        try {
            $ids = $this->getComputerPlayersIds();
            if ($ids) {
                $select->where('a."playerId" NOT IN (?)', new Zend_Db_Expr($ids));
            }
            $result = $this->_db->query($select)->fetchAll();
            return $result[0]['min'];
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getComputerPlayersIds()
    {
        $ids = '';
        try {
            $select = $this->_db->select()
                ->from(array('a' => 'playersingame'), 'playerId')
                ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
                ->where('a."gameId" = ?', $this->_gameId)
                ->where($this->_db->quoteIdentifier('mapPlayerId') . ' is not null')
                ->where('computer = true');
            $result = $this->_db->query($select)->fetchAll();
            foreach ($result as $row) {
                if ($ids) {
                    $ids .= ',';
                }
                $ids .= $row['playerId'];
            }
            return $ids;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getPlayerInGame($playerId)
    {
        try {
            $select = $this->_db->select()
                ->from('playersingame')
                ->where('"gameId" = ?', $this->_gameId)
                ->where('"playerId" = ?', $playerId);
            $result = $this->_db->query($select)->fetchAll();
            return $result[0];
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function isColorInGame($playerId, $mapPlayerId)
    {
        $select = $this->_db->select()
            ->from('playersingame', 'mapPlayerId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" != ?', $playerId)
            ->where($this->_db->quoteIdentifier('mapPlayerId') . ' = ?', $mapPlayerId);
        try {
            return $this->_db->fetchOne($select);
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function updatePlayerReady($playerId, $mapPlayerId)
    {
        if ($this->isColorInGame($playerId, $mapPlayerId)) {
            return false;
        }
        $player = $this->getPlayerInGame($playerId);
        if ($player['mapPlayerId'] == $mapPlayerId) {
            $data['mapPlayerId'] = null;
        } else {
            $data['mapPlayerId'] = $mapPlayerId;
        }

        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);

        $this->_db->update('playersingame', $data, $where);
    }

    public function isPlayerTurn($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('turnPlayerId'))
            ->where('"turnPlayerId" = ?', $playerId)
            ->where('"' . $this->_primary . '" = ?', $this->_gameId);
        $result = $this->_db->query($select)->fetchAll();
        if (isset($result[0]['turnPlayerId'])) {
            return true;
        } else {
            return false;
        }
    }

    public function getTurnPlayerId()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'turnPlayerId')
            ->where('"' . $this->_primary . '" = ?', $this->_gameId);

        return $this->selectOne($select);
    }

    public function updateTurnNumber($nextPlayer)
    {
        $playerColors = Zend_Registry::get('colors');

        if ($playerColors[0] == $nextPlayer['color']) { //first color
            $select = $this->_db->select()
                ->from('game', array('turnNumber' => '("turnNumber" + 1)'))
                ->where('"gameId" = ?', $this->_gameId);

            $turnNumber = $this->selectOne($select);

            $data = array(
                'turnNumber' => $turnNumber,
                'end' => new Zend_Db_Expr('now()'),
                'turnPlayerId' => $nextPlayer['playerId']
            );
        } else {
            $data = array(
                'turnPlayerId' => $nextPlayer['playerId']
            );
        }

        $this->updateGame($data);
    }

    public function endGame()
    {
        $data['isActive'] = 'false';

        $this->updateGame($data);
    }

    public function getTurn()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('nr' => 'turnNumber'))
            ->join(array('b' => 'playersingame'), 'a."turnPlayerId" = b."playerId" AND a."gameId" = b."gameId"', array('lost'))
            ->join(array('c' => 'mapplayers'), 'b . "mapPlayerId" = c . "mapPlayerId"', array('color' => 'shortName'))
            ->where('a."' . $this->_primary . '" = ?', $this->_gameId);

        return $this->selectRow($select);
    }

    public function playerTurnActive($playerId)
    {
        try {
            $select = $this->_db->select()
                ->from('playersingame', 'turnActive')
                ->where('"playerId" = ?', $playerId)
                ->where('"turnActive" = ?', true)
                ->where('"' . $this->_primary . '" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0]['turnActive'])) {
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function playerLost($playerId)
    {
        try {
            $select = $this->_db->select()
                ->from('playersingame', 'lost')
                ->where('"playerId" = ?', $playerId)
                ->where('lost = ?', true)
                ->where('"' . $this->_primary . '" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0]['lost'])) {
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function turnActivate($playerId)
    {
        $data = array(
            'turnActive' => 'true'
        );
        $where = array(
            $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId),
            $this->_db->quoteInto('"playerId" = ?', $playerId)
        );
        $this->_db->update('playersingame', $data, $where);
        $data['turnActive'] = 'false';
        $where = array(
            $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId),
            $this->_db->quoteInto('"turnActive" = ?', 'true'),
            $this->_db->quoteInto('"playerId" != ?', $playerId)
        );
        $this->_db->update('playersingame', $data, $where);
    }

    public function getTurnNumber()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'turnNumber')
            ->where('"' . $this->_primary . '" = ?', $this->_gameId);

        return $this->selectOne($select);
    }

    public function getPlayerInGameGold($playerId)
    {
        try {
            $select = $this->_db->select()
                ->from('playersingame', 'gold')
                ->where('"playerId" = ?', $playerId)
                ->where('"' . $this->_primary . '" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            return $result[0]['gold'];
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function updatePlayerInGameGold($playerId, $gold)
    {
        $data['gold'] = $gold;
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $this->_db->update('playersingame', $data, $where);
    }

    public function setPlayerLostGame($playerId)
    {
        $data['lost'] = 'true';
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $this->_db->update('playersingame', $data, $where);
    }

    public function getAccessKey($playerId)
    {
        $select = $this->_db->select()
            ->from('playersingame', 'accessKey')
            ->where('"playerId" = ?', $playerId)
            ->where('"' . $this->_primary . '" = ?', $this->_gameId);
        return $this->_db->fetchOne($select);
    }

    public function getNumberOfPlayers()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'numberOfPlayers')
            ->where('"' . $this->_primary . '" = ?', $this->_gameId);
        return $this->_db->fetchOne($select);
    }

    public function getMapId()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'mapId')
            ->where($this->_db->quoteIdentifier($this->_primary) . ' = ?', $this->_gameId);
        return $this->_db->fetchOne($select);
    }

}

