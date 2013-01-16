<?php

class Application_Model_Turn extends Application_Model_Database {

    private static $playerColors = array('white', 'yellow', 'green', 'red', 'orange');

    static public function next($gameId, $playerId, $playerColor) {
        if (self::playerLost($gameId, $playerId)) {
            return null;
        }
        if (self::isPlayerTurn($gameId, $playerId)) {
            $youWin = false;
            $response = array();
            $nextPlayer = array(
                'color' => $playerColor
            );
            while (empty($response))
            {
                $nextPlayer = self::nextTurn($gameId, $nextPlayer['color']);
                $playerCastlesExists = self::playerCastlesExists($gameId, $nextPlayer['playerId']);
                $playerArmiesExists = self::playerArmiesExists($gameId, $nextPlayer['playerId']);
                if ($playerCastlesExists || $playerArmiesExists) {
                    $response = $nextPlayer;
                    if ($nextPlayer['playerId'] == $playerId) {
                        $youWin = true;
                        self::endGame($gameId);
                    } else {
                        self::updateTurnNumber($gameId, $nextPlayer['playerId']);
                        self::raiseAllCastlesProductionTurn($gameId, $playerId);
                        $nextTurn = self::getTurn($gameId);
                        $response['lost'] = $nextTurn['lost'];
                        $response['nr'] = $nextTurn['nr'];
//                        $mWebSocket->publishChannel($gameId, $playerColor . '.t.' . $nextTurn['color'] . '.' . $nextTurn['nr'] . '.' . $nextTurn['lost']);
                    }
                    $response['win'] = $youWin;
                } else {
                    self::setPlayerLostGame($gameId, $nextPlayer['playerId']);
                }
            }

            return $response;
        }
    }

    static private function playerLost($gameId, $playerId) {
        $db = parent::getDb();
        $select = $db->select()
                ->from('playersingame', 'lost')
                ->where('"playerId" = ?', $playerId)
                ->where('lost = ?', true)
                ->where('"gameId" = ?', $gameId);
        try {
            return $db->fetchOne($select);
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    static private function nextTurn($gameId, $playerColor) {
        $find = false;
        // szukam następnego koloru w dostępnych kolorach
        foreach (self::$playerColors as $color)
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
            echo('Nie znalazłem koloru gracza');
        }
        $playersInGame = self::getPlayersInGameReady($gameId);
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
                if ($player['color'] == self::$playerColors[0]) {
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
            echo('Nie znalazłem gracza');
        }
        return array('playerId' => $nextPlayerId, 'color' => $nextPlayerColor);
    }

    static private function getPlayersInGameReady($gameId) {
        $db = parent::getDb();
        $select = $db->select()
                ->from(array('a' => 'playersingame'))
                ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', array('computer'))
                ->where('ready = true')
                ->where('a."gameId" = ?', $gameId);
        try {
            return $db->query($select)->fetchAll();
        } catch (PDOException $e) {
            echo($select->__toString());
        }
    }

    static private function playerCastlesExists($gameId, $playerId) {
        $db = parent::getDb();
        $select = $db->select()
                ->from('castle', 'castleId')
                ->where('"playerId" = ?', $playerId)
                ->where('"gameId" = ?', $gameId)
                ->where('razed = false');
        try {
            if (count($db->query($select)->fetchAll())) {
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    static private function playerArmiesExists($gameId, $playerId) {
        $db = parent::getDb();
        $select = $db->select()
                ->from('army', 'armyId')
                ->where('"gameId" = ?', $gameId)
                ->where('destroyed = false')
                ->where('"playerId" = ?', $playerId);
        try {
            if (count($db->query($select)->fetchAll())) {
                return true;
            }
        } catch (Exception $e) {
            echo($select->__toString());
        }
    }

    static private function endGame($gameId) {
        $data['isActive'] = 'false';
        return self::updateGame($gameId, $data);
    }

    static private function updateTurnNumber($gameId, $playerId) {
        $db = parent::getDb();
        if (self::isGameMaster($gameId, $playerId)) {
            $select = $db->select()
                    ->from('game', array('turnNumber' => '("turnNumber" + 1)'))
                    ->where('"gameId" = ?', $gameId);
            try {
                $result = $db->fetchRow($select);
                $data = array(
                    'turnNumber' => $result['turnNumber'],
                    'end' => new Zend_Db_Expr('now()')
                );
            } catch (Exception $e) {
                echo($e);
                echo($select->__toString());
            }
        }
        $data['turnPlayerId'] = $playerId;

        try {
            self::updateGame($gameId, $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static private function isGameMaster($gameId, $playerId) {
        $db = parent::getDb();
        $select = $db->select()
                ->from('game', array('gameMasterId'))
                ->where('"gameId" = ?', $gameId)
                ->where('"gameMasterId" = ?', $playerId);

        if ($playerId == $db->fetchOne($select)) {
            return true;
        }
    }

    static private function updateGame($gameId, $data) {
        $db = parent::getDb();
        $where = $db->quoteInto('"gameId" = ?', $gameId);
        return $db->update('game', $data, $where);
    }

    static private function raiseAllCastlesProductionTurn($gameId, $playerId) {
        $db = parent::getDb();
        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId)
        );
        $data = array(
            'productionTurn' => new Zend_Db_Expr('"productionTurn" + 1')
        );
        return $db->update('castle', $data, $where);
    }

    static public function getTurn($gameId) {
        $db = parent::getDb();
        $select = $db->select()
                ->from(array('a' => 'game'), array('nr' => 'turnNumber'))
                ->join(array('b' => 'playersingame'), 'a."turnPlayerId" = b."playerId" AND a."gameId" = b."gameId"', array('color', 'lost'))
                ->where('a."gameId" = ?', $gameId);
        try {
            return $db->fetchRow($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static private function setPlayerLostGame($gameId, $playerId) {
        $db = parent::getDb();
        $data['lost'] = 'true';
        $where = array(
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"playerId" = ?', $playerId)
        );
        $db->update('playersingame', $data, $where);
    }

}
