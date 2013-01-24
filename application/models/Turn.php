<?php

class Application_Model_Turn extends Application_Model_Database {

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
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

}
