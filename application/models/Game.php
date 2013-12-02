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
//            ->join(array('c' => 'mapplayers'), 'b . "mapPlayerId" = c . "mapPlayerId"', null)
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

        return $this->selectRow($select);
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

    public function isPlayerTurn($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'turnPlayerId')
            ->where('"turnPlayerId" = ?', $playerId)
            ->where('"' . $this->_primary . '" = ?', $this->_gameId);

        return $this->selectOne($select);
    }

    public function getTurnPlayerId()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'turnPlayerId')
            ->where('"' . $this->_primary . '" = ?', $this->_gameId);

        return $this->selectOne($select);
    }

    public function updateTurnNumber($nextPlayerId, $nextPlayerColor)
    {
        $playerColors = Zend_Registry::get('playersInGameColors');
        reset($playerColors);

        if (current($playerColors) == $nextPlayerColor) { //first color, turn number increment
            $select = $this->_db->select()
                ->from('game', array('turnNumber' => '("turnNumber" + 1)'))
                ->where('"gameId" = ?', $this->_gameId);

            $turnNumber = $this->selectOne($select);

            $data = array(
                'turnNumber' => $turnNumber, // zamienić na new Zend_Db_Expr($select->_toString()),
                'end' => new Zend_Db_Expr('now()'),
                'turnPlayerId' => $nextPlayerId
            );
        } else {
            $data = array(
                'turnPlayerId' => $nextPlayerId
            );
        }

        $this->updateGame($data);
    }

    public function endGame()
    {
        $data['isActive'] = 'false';

        $this->updateGame($data);
    }

    public function getTurnNumber()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'turnNumber')
            ->where('"' . $this->_primary . '" = ?', $this->_gameId);

        return $this->selectOne($select);
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

