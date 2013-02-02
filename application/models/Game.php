<?php

class Application_Model_Game extends Game_Db_Table_Abstract {

    protected $_name = 'game';
    protected $_primary = 'gameId';
    protected $_sequence = "game_gameId_seq";
    protected $_id;
    protected $_playerColors = array('white', 'yellow', 'green', 'red', 'orange');

    public function __construct($gameId = 0) {
        $this->_gameId = $gameId;
        parent::__construct();
    }

    private function generateKey() {
        return md5(rand(0, time()));
    }

    public function createGame($numberOfPlayers, $playerId) {
        $data = array(
            'numberOfPlayers' => $numberOfPlayers,
            'gameMasterId' => $playerId,
            'lAccessKey' => $this->generateKey(),
            'lSecretKey' => $this->generateKey()
        );

        $this->_db->insert($this->_name, $data);
        $seq = $this->_db->quoteIdentifier($this->_sequence);
        return $this->_db->lastSequenceId($seq);
    }

    public function isActive() {
        $select = $this->_db->select()
                ->from($this->_name, $this->_primary)
                ->where('"isActive" = true')
                ->where('"' . $this->_primary . '" = ?', $this->_gameId);
        $result = $this->_db->query($select)->fetchAll();
        if (!empty($result[0][$this->_primary]))
            return true;
    }

    public function getOpen() {
        $select = $this->_db->select()
                ->from($this->_name)
                ->where('"isOpen" = true')
                ->order('begin DESC');
        $result = $this->_db->query($select)->fetchAll();
        foreach ($result as $k => $game)
        {
            $select = $this->_db->select()
                    ->from('playersingame', 'count(*)')
                    ->where('"gameId" = ?', $game['gameId'])
                    ->where('"timeout" > (SELECT now() - interval \'10 seconds\')');
            $playersingame = $this->_db->query($select)->fetchAll();
            if ($playersingame[0]['count'] > 0) {
                $result[$k]['playersingame'] = $playersingame[0]['count'];
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

    public function getMyGames($playerId) {

        $select1 = $this->_db->select()
                ->from('playersingame', $this->_primary)
                ->where('ready = true')
                ->where('lost = false')
                ->where('"playerId" = ?', $playerId);
        $select2 = $this->_db->select()
                ->from(array('a' => $this->_name))
                ->join(array('b' => 'playersingame'), 'a."gameId" = b."gameId"', array('color'))
                ->where('"isOpen" = false')
                ->where('"isActive" = true')
                ->where('a."gameId" IN ?', $select1)
                ->where('b."playerId" = ?', $playerId)
                ->order('begin DESC');
        try {
            $result = $this->_db->query($select2)->fetchAll();
            foreach (array_keys($result) as $k)
            {
                $players = array();

                $select = $this->_db->select()
                        ->from(array('a' => 'player'), array('firstName', 'lastName', 'playerId'))
                        ->join(array('b' => 'playersingame'), 'a."playerId" = b."playerId"', array('color'))
                        ->where('"gameId" = ?', $result[$k]['gameId']);
                foreach ($this->_db->query($select)->fetchAll() as $v)
                {
                    $players[$v['playerId']] = $v;
                }
                $result[$k]['players'] = $players;
            }
            return $result;
        } catch (PDOException $e) {
            throw new Exception($select2->__toString());
        }
    }

    public function getAlivePlayers() {
        $select = $this->_db->select()
                ->from(array('a' => 'playersingame'), array('lost', 'color'))
                ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', array('firstName', 'lastName', 'playerId'))
                ->where('ready = true')
                ->where('color IS NOT NULL')
                ->where('"' . $this->_primary . '" = ?', $this->_gameId); //throw new Exception($select->__toString());
        try {
            return $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function playerIsAlive($playerId) {
        try {
            $select = $this->_db->select()
                    ->from('playersingame', 'playerId')
                    ->where('ready = true')
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

    public function startGame() {
        $data = array(
            'turnPlayerId' => $this->getPlayerIdByColor('white'),
            'isOpen' => 'false'
        );
        $this->updateGame($data);
    }

    public function getGame() {
        $select = $this->_db->select()
                ->from($this->_name)
                ->where('"' . $this->_primary . '" = ?', $this->_gameId);
        return $this->_db->fetchRow($select);
    }

    public function getAllColors() {
        return $this->_playerColors;
    }

    public function getPlayerColor($playerId) {
        try {
            $select = $this->_db->select()
                    ->from('playersingame', 'color')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" = ?', $playerId);
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0]['color'])) {
                return $result[0]['color'];
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getPlayerIdByColor($color) {
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

    public function isPlayerInGame($playerId) {
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

    public function joinGame($playerId) {
        $data = array(
            'gameId' => $this->_gameId,
            'playerId' => $playerId
        );
        $this->_db->insert('playersingame', $data);
    }

    public function disconnectFromGame($gameId, $playerId) {
        if (empty($gameId)) {
            $gameId = $this->_gameId;
        }
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $this->_db->delete('playersingame', $where);
    }

    public function disconnectNotActive() {
        $select = $this->_db->select()
                ->from('playersingame', 'playerId')
                ->where('"' . $this->_primary . '" = ?', $this->_gameId)
                ->where('"timeout" < (SELECT now() - interval \'20 seconds\')');
        $where[] = $this->_db->quoteInto('"playerId" IN (?)', new Zend_Db_Expr($select->__toString()));
        $this->_db->delete('playersingame', $where);
    }

    public function updatePlayerInGame($playerId) {
        $data = array(
            'timeout' => 'now()'
        );
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId);
        return $this->_db->update('playersingame', $data, $where);
    }

    public function updatePlayerInGameWSSUId($playerId, $wssuid) {
        $data = array(
            'webSocketServerUserId' => $wssuid
        );
        $where = array(
            $this->_db->quoteInto('"playerId" = ?', $playerId),
            $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId)
        );
        return $this->_db->update('playersingame', $data, $where);
    }

    public function getGameMaster() {
        try {
            $select = $this->_db->select()
                    ->from($this->_name, 'gameMasterId')
                    ->where('"' . $this->_primary . '" = ?', $this->_gameId);
            return $this->_db->fetchOne($select);
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function updateGameMaster($playerId) {
        if (!$this->isPlayerWaitingForGame($this->getGameMaster())) {
            $data = array(
                'gameMasterId' => $playerId
            );
            $this->updateGame($data);
        }
    }

    public function isGameMaster($playerId) {
        $select = $this->_db->select()
                ->from($this->_name, array('gameMasterId'))
                ->where('"' . $this->_primary . '" = ?', $this->_gameId)
                ->where('"gameMasterId" = ?', $playerId);
        $result = $this->_db->query($select)->fetchAll();
        if (isset($result[0]['gameMasterId'])) {
            if ($playerId == $result[0]['gameMasterId']) {
                return true;
            }
        }
    }

    public function isGameStarted() {
        $select = $this->_db->select()
                ->from($this->_name, array('isOpen'))
                ->where('"isOpen" = false')
                ->where('"' . $this->_primary . '" = ?', $this->_gameId);
        $result = $this->_db->query($select)->fetchAll();
        if (isset($result[0]['isOpen'])) {
            return true;
        } else {
            return false;
        }
    }

    public function updateGame($data) {
        $where = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId);
        return $this->_db->update($this->_name, $data, $where);
    }

//    public function getPlayersInGame() {
//        try {
//            $select = $this->_db->select()
//                    ->from('playersingame')
//                    ->where('"gameId" = ?', $this->_gameId);
//            return $this->_db->query($select)->fetchAll();
//        } catch (PDOException $e) {
//            throw new Exception($select->__toString());
//        }
//    }

    public function getPlayersInGameReady() {
        $select = $this->_db->select()
                ->from(array('a' => 'playersingame'))
                ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', array('computer'))
                ->where('ready = true')
                ->where('a."gameId" = ?', $this->_gameId);
        try {
            return $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getPlayersInGameLoad() {
        $select = $this->_db->select()
                ->from(array('a' => 'playersingame'), array('ready', 'color', 'playerId'))
                ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', array('firstName', 'lastName', 'computer'))
                ->join(array('c' => 'game'), 'a."gameId" = c."gameId"', 'gameMasterId')
                ->where('a."gameId" = ?', $this->_gameId)
                ->where('color IS NOT NULL')
                ->where('"timeout" > (SELECT now() - interval \'10 seconds\') OR computer = true');
        try {
            $array = $this->_db->query($select)->fetchAll();
            $result = array();
            foreach ($array as $value)
            {
                $result[$value['color']] = $value;
            }
            return $result;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getComputerPlayerId() {
        $select = $this->_db->select()
                ->from(array('a' => 'playersingame'), 'min(b."playerId")')
                ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
                ->where('a."gameId" != ?', $this->_gameId)
                ->where('ready = true')
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

    public function getComputerPlayers() {
        try {
            $select = $this->_db->select()
                    ->from(array('a' => 'playersingame'), array('color', 'playerId'))
                    ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', array('computer'))
                    ->where('a."gameId" = ?', $this->_gameId)
                    ->where('ready = true')
                    ->where('computer = true');
            return $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getComputerPlayersIds() {
        $ids = '';
        try {
            $select = $this->_db->select()
                    ->from(array('a' => 'playersingame'), 'playerId')
                    ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', null)
                    ->where('a."gameId" = ?', $this->_gameId)
                    ->where('ready = true')
                    ->where('computer = true');
            $result = $this->_db->query($select)->fetchAll();
            foreach ($result as $row)
            {
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

    public function getPlayersWaitingForGame() {
        try {
            $select = $this->_db->select()
                    ->from(array('a' => 'playersingame'), array('ready', 'color', 'playerId'))
                    ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', array('firstName', 'lastName', 'computer'))
                    ->join(array('c' => 'game'), 'a."gameId" = c."gameId"', 'gameMasterId')
                    ->where('a."gameId" = ?', $this->_gameId)
                    ->where('"timeout" > (SELECT now() - interval \'10 seconds\') OR computer = true');
            return $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function isPlayerWaitingForGame($playerId) {
        try {
            $select = $this->_db->select()
                    ->from(array('a' => 'playersingame'), 'playerId')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" = ?', $playerId)
                    ->where('"timeout" > (SELECT now() - interval \'10 seconds\')');
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0]['playerId'])) {
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getPlayerInGame($playerId) {
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

    public function isPlayerReady($playerId) {
        try {
            $select = $this->_db->select()
                    ->from('playersingame', 'ready')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" = ?', $playerId)
                    ->where('ready = true');
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0]['ready'])) {
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function isColorInGame($playerId, $color) {
        try {
            $select = $this->_db->select()
                    ->from('playersingame', 'color')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" != ?', $playerId)
                    ->where('color = ?', $color)
                    ->where('"timeout" > (SELECT now() - interval \'10 seconds\')');
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0]['color'])) {
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function updatePlayerReady($playerId, $color) {
        if ($this->isColorInGame($playerId, $color)) {
            return false;
        }
        $player = $this->getPlayerInGame($playerId);
        if ($player['ready'] && $player['color'] == $color) {
            $data['ready'] = 'false';
            $data['color'] = null;
        } else {
            $data['ready'] = 'true';
            $data['color'] = $color;
        }
        $data['timeout'] = 'now()';
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $result = $this->_db->update('playersingame', $data, $where);
        if ($result == 1) {
            return array('ready' => $data['ready']);
        }
    }

    public function updateComputerPlayersReady() {
        $ids = $this->getComputerPlayersIds();
        if ($ids) {
            $data['timeout'] = 'now()';
            $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId);
            $where[] = $this->_db->quoteInto('"playerId" IN (?)', new Zend_Db_Expr($ids));
            $this->_db->update('playersingame', $data, $where);
        }
    }

    public function kick($colorKick, $playerId) {
        if ($this->isGameMaster($playerId)) {
            $playerId = $this->getPlayerIdByColor($colorKick);
            if ($playerId) {
                return $this->disconnectFromGame($this->_gameId, $playerId);
            }
        }
    }

    public function isPlayerTurn($playerId) {
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

    public function getTurnPlayerId() {
        $select = $this->_db->select()
                ->from($this->_name, 'turnPlayerId')
                ->where('"' . $this->_primary . '" = ?', $this->_gameId);
        $result = $this->_db->query($select)->fetchAll();
        return $result[0]['turnPlayerId'];
    }

    public function nextTurn($playerColor) {
        $find = false;
        // szukam następnego koloru w dostępnych kolorach
        foreach ($this->_playerColors as $color)
        {
            if ($playerColor == $color) {
                $find = true;
                continue;
            }
            if ($find) {
                $nextPlayerColor = $color;
                break;
            }
        }
        if (!isset($nextPlayerColor)) {
            throw new Exception('Nie znalazłem koloru gracza');
        }
        $playersInGame = $this->getPlayersInGameReady();
        // przypisuję playerId do koloru
        foreach ($playersInGame as $k => $player)
        {
            if ($player['color'] == $nextPlayerColor) {
                $nextPlayerId = $player['playerId'];
                break;
            }
        }
        // jeśli nie znalazłem następnego gracza to następnym graczem jest gracz pierwszy
        if (!isset($nextPlayerId)) {
            foreach ($playersInGame as $k => $player)
            {
                if ($player['color'] == $this->_playerColors[0]) {
                    if ($player['lost']) {
                        $nextPlayerId = $playersInGame[$k + 1]['playerId'];
                        $nextPlayerColor = $playersInGame[$k + 1]['color'];
                    } else {
                        $nextPlayerId = $player['playerId'];
                        $nextPlayerColor = $player['color'];
                    }
                    break;
                }
            }
        }
        if (!isset($nextPlayerId)) {
            throw new Exception('Nie znalazłem gracza');
        }
        return array('playerId' => $nextPlayerId, 'color' => $nextPlayerColor);
    }

    public function updateTurnNumber($playerId) {
        if ($this->isGameMaster($playerId)) {
            $select = $this->_db->select()
                    ->from($this->_name, array('turnNumber' => '("turnNumber" + 1)'))
                    ->where('"' . $this->_primary . '" = ?', $this->_gameId);
            $result = $this->_db->fetchRow($select);
            $data = array(
                'turnNumber' => $result['turnNumber'],
                'end' => new Zend_Db_Expr('now()')
            );
        }
        $data['turnPlayerId'] = $playerId;

        if ($this->updateGame($data) == 1) {
            if (isset($data['turnNumber']) && Zend_Validate::is($data['turnNumber'], 'Digits')) {
                return $data['turnNumber'];
            }
        } else {
            throw new Exception('Błąd zapytania!');
        }
    }

    public function endGame() {
        $data['isActive'] = 'false';

        $this->updateGame($data);
    }

    public function getTurn() {
        $select = $this->_db->select()
                ->from(array('a' => $this->_name), array('nr' => 'turnNumber'))
                ->join(array('b' => 'playersingame'), 'a."turnPlayerId" = b."playerId" AND a."gameId" = b."gameId"', array('color', 'lost'))
                ->where('a."' . $this->_primary . '" = ?', $this->_gameId);
        try {
            return $this->_db->fetchRow($select);
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function playerTurnActive($playerId) {
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

    public function playerLost($playerId) {
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

    public function turnActivate($playerId) {
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

    public function getTurnNumber() {
        $select = $this->_db->select()
                ->from($this->_name, 'turnNumber')
                ->where('"' . $this->_primary . '" = ?', $this->_gameId);
        try {
            return $this->_db->fetchOne($select);
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getPlayerInGameGold($playerId) {
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

    public function updatePlayerInGameGold($playerId, $gold) {
        $data['gold'] = $gold;
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $this->_db->update('playersingame', $data, $where);
    }

    public function setPlayerLostGame($playerId) {
        $data['lost'] = 'true';
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $this->_db->update('playersingame', $data, $where);
    }

    public function updateAccessKey() {
        $data = array(
            'lAccessKey' => $this->generateKey()
        );
        $this->updateGame($data);
    }

    public function getKeys() {
        $select = $this->_db->select()
                ->from($this->_name, array('gameId', 'lAccessKey', 'lSecretKey'))
                ->where('"' . $this->_primary . '" = ?', $this->_gameId);
        return $this->_db->fetchRow($select);
    }

}

