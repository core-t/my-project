<?php

class Application_Model_Game extends Warlords_Db_Table_Abstract {

    protected $_name = 'game';
    protected $_primary = 'gameId';
    protected $_sequence = "game_gameId_seq";
    protected $_db;
    protected $_id;
    protected $_playerColors = array('white', 'green', 'yellow', 'red', 'orange');

    public function __construct($gameId = 0) {
        $this->_gameId = $gameId;
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function createGame($data) {
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
        foreach($result as $k=>$game){
            $select = $this->_db->select()
                    ->from('playersingame', 'count(*)')
                    ->where('"gameId" = ?', $game['gameId'])
                    ->where('"timeout" > (SELECT now() - interval \'10 seconds\')');
            $playersingame = $this->_db->query($select)->fetchAll();
            $result[$k]['playersingame'] = $playersingame[0]['count'];
        }
        return $result;
    }

    public function getMyGames($playerId) {
        try {
            $select1 = $this->_db->select()
                        ->from('playersingame', $this->_primary)
                        ->where('ready = true')
                        ->where('lost = false')
                        ->where('"playerId" = ?', $playerId);
            $select2 = $this->_db->select()
                    ->from($this->_name)
                    ->where('"isOpen" = false')
                    ->where('"isActive" = true')
                    ->where('"gameId" IN ?', $select1)
                    ->order('begin DESC');
            $result = $this->_db->query($select2)->fetchAll();
            return $result;
        } catch (PDOException $e) {
            throw new Exception($select2->__toString());
        }
    }

    public function getAlivePlayers() {
        try {
            $select = $this->_db->select()
                        ->from('playersingame', 'playerId')
                        ->where('ready = true')
                        ->where('lost = false')
                        ->where('"' . $this->_primary . '" = ?', $this->_gameId);
            return $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function startGame() {
        $data['isOpen'] = 'false';
        $where = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId);
        return $this->_db->update($this->_name, $data, $where);
    }

    public function getGame() {
        $select = $this->_db->select()
                ->from($this->_name)
                ->where('"' . $this->_primary . '" = ?', $this->_gameId);
        $result = $this->_db->query($select)->fetchAll();
        if (is_array($result[0]))
            return $result[0];
    }

    public function getAllColors() {
        return $this->_playerColors;
    }

    public function getPlayersWaitingForGame() {
        try {
            $select = $this->_db->select()
                    ->from('playersingame')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"timeout" > (SELECT now() - interval \'10 seconds\')');
            return $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getPlayerColor($id) {
        try {
            $select = $this->_db->select()
                    ->from('playersingame', 'color')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" = ?', $id);
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0]['color'])) {
                return $result[0]['color'];
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getPlayerIdByColor($color) {
        try {
            $select = $this->_db->select()
                    ->from('playersingame', 'playerId')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('color = ?', $color);
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0]['playerId'])) {
                return $result[0]['playerId'];
            }
        } catch (PDOException $e) {
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
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
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

    public function getPlayersInGame() {
        try {
            $select = $this->_db->select()
                    ->from('playersingame')
                    ->where('"gameId" = ?', $this->_gameId);
            return $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getPlayerInGame($playerId){
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

    public function isPlayerReady($playerId){
        try {
            $select = $this->_db->select()
                    ->from('playersingame', 'ready')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" = ?', $playerId)
                    ->where('ready = true');
            $result = $this->_db->query($select)->fetchAll();
            if(isset($result[0]['ready'])){
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function isColorInGame($playerId, $color){
        try {
            $select = $this->_db->select()
                    ->from('playersingame', 'color')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" != ?', $playerId)
                    ->where('color = ?', $this->_playerColors[$color])
                    ->where('"timeout" > (SELECT now() - interval \'10 seconds\')');
            $result = $this->_db->query($select)->fetchAll();
            if(isset($result[0]['color'])){
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function updatePlayerReady($playerId, $color) {
        if($this->isColorInGame($playerId, $color)){
            return false;
        }
        $player = $this->getPlayerInGame($playerId);
        if ($player['ready'] && $player['color'] == $this->_playerColors[$color])
            $data['ready'] = 'false';
        else
            $data['ready'] = 'true';
        $data['timeout'] = 'now()';
        $data['color'] = $this->_playerColors[$color];
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $result = $this->_db->update('playersingame', $data, $where);
        if($result == 1){
            return array('ready' => $data['ready'], 'color' => $color);
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

    public function nextTurn($playerColor) {
        $find = false;
        foreach ($this->_playerColors as $color) {
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
        $playersInGame = $this->getPlayersInGame();
        foreach ($playersInGame as $k => $player) {
            if ($player['color'] == $nextPlayerColor) {
                $nextPlayerId = $player['playerId'];
            }
        }
        if (!isset($nextPlayerId)) {
            foreach ($playersInGame as $k => $player) {
                if ($player['color'] == 'white') {
                    $nextPlayerId = $player['playerId'];
                    $nextPlayerColor = $player['color'];
                }
            }
        }
        if (!isset($nextPlayerId)) {
            throw new Exception('Nie znalazłem gracza');
        }
        return array('playerId' => $nextPlayerId, 'color' => $nextPlayerColor);
    }

    public function updateTurnNumber($playerId){
        if($this->isGameMaster($playerId)) {
            $select = $this->_db->select()
                    ->from($this->_name, array('turnNumber' => '("turnNumber" + 1)'))
                    ->where('"' . $this->_primary . '" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            $data['turnNumber'] = $result[0]['turnNumber'];
        }
        $data['turnPlayerId'] = $playerId;

        if ($this->updateGame($data) == 1) {
            if(isset($data['turnNumber'])){
                return $data['turnNumber'];
            }
        } else {
            throw new Exception('Błąd zapytania!');
        }
    }

    public function endGame(){
        $data['isActive'] = 'false';

        $this->updateGame($data);
    }

    public function getTurn() {
        try {
            $select = $this->_db->select()
                    ->from(array('a' => $this->_name), array('nr' => 'turnNumber'))
                    ->join(array('b' => 'playersingame'), 'a."turnPlayerId" = b."playerId" AND a."gameId" = b."gameId"', 'color')
                    ->where('a."' . $this->_primary . '" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0])) {
                $result[0]['lost'] = 0;
                return $result[0];
            }
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
        $data['turnActive'] = 'true';
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $this->_db->update('playersingame', $data, $where);
        $where = array();
        $data['turnActive'] = 'false';
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"turnActive" = ?', 'true');
        $where[] = $this->_db->quoteInto('"playerId" != ?', $playerId);
        $this->_db->update('playersingame', $data, $where);
    }

    public function getTurnNumber() {
        try {
            $select = $this->_db->select()
                    ->from($this->_name, 'turnNumber')
                    ->where('"' . $this->_primary . '" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            return $result[0]['turnNumber'];
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getPlayerInGameGold($playerId){
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

    public function updatePlayerInGameGold($playerId, $gold){
        $data['gold'] = $gold;
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $this->_db->update('playersingame', $data, $where);
    }

    public function setPlayerLostGame($playerId){
        $data['lost'] = 'true';
        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $this->_db->update('playersingame', $data, $where);
    }
}

