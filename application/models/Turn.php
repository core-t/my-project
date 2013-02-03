<?php

class Application_Model_Turn {

    static public function next($gameId, $playerId, $playerColor) {
        if (Application_Model_Database::playerLost($gameId, $playerId)) {

            return;
        }
        
        $youWin = false;
        $response = array();
        $nextPlayer = array(
            'color' => $playerColor
        );
        while (empty($response))
        {
            $nextPlayer = Application_Model_Database::nextTurn($gameId, $nextPlayer['color']);
            $playerCastlesExists = Application_Model_Database::playerCastlesExists($gameId, $nextPlayer['playerId']);
            $playerArmiesExists = Application_Model_Database::playerArmiesExists($gameId, $nextPlayer['playerId']);
            if ($playerCastlesExists || $playerArmiesExists) {
                $response = $nextPlayer;
                if ($nextPlayer['playerId'] == $playerId) {
                    $youWin = true;
                    Application_Model_Database::endGame($gameId);
                } else {
                    Application_Model_Database::updateTurnNumber($gameId, $nextPlayer['playerId']);
                    Application_Model_Database::raiseAllCastlesProductionTurn($gameId, $playerId);
                    $nextTurn = Application_Model_Database::getTurn($gameId);
                    $response['lost'] = $nextTurn['lost'];
                    $response['nr'] = $nextTurn['nr'];
                }
                $response['win'] = $youWin;
            } else {
                Application_Model_Database::setPlayerLostGame($gameId, $nextPlayer['playerId']);
            }
        }

        return $response;
    }

}
