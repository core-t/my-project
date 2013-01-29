<?php

class Application_Model_Move {

    private $canFly = 1;
    private $canSwim = 0;

    public function go($gameId, $armyId, $x, $y, $playerId, $db = null) {
        $army = Application_Model_Database::getArmyByArmyIdPlayerId($gameId, $armyId, $playerId, $db);
        $this->fields = Application_Model_Database::getEnemyArmiesFieldsPositions($gameId, $playerId, $db);

        $currentPosition = $this->calculateNewArmyPosition($gameId, $army, $x, $y, $playerId, $db);

        if (!$currentPosition) {
            echo('Nie wykonano ruchu');
            return;
        }
        if ($currentPosition['movesSpend'] > $army['movesLeft']) {
            echo('Próba wykonania większej ilości ruchów niż jednostka posiada');
            return;
        }
        Application_Model_Database::updateArmyPosition($gameId, $armyId, $playerId, $currentPosition);
        $armiesIds = Application_Model_Database::joinArmiesAtPosition($gameId, $currentPosition, $playerId);
        $newArmyId = $armiesIds['armyId'];

        return array(
            'attackerColor' => Application_Model_Database::getPlayerColor($gameId, $playerId, $db),
            'attackerArmy' => Application_Model_Database::getArmyByArmyIdPlayerId($gameId, $newArmyId, $playerId, $db),
            'path' => $this->path,
            'oldArmyId' => $armyId,
            'deletedIds' => $armiesIds['deletedIds'],
        );
    }

    private function calculateNewArmyPosition($gameId, $army, $destX, $destY, $playerId, $db = null) {
        $this->canFly -= count($army['heroes']);
        foreach ($army['soldiers'] as $soldier)
        {
            if ($soldier['canFly']) {
                $this->canFly++;
            } else {
                $this->canFly -= 200;
            }
            if ($soldier['canSwim']) {
                $this->canSwim++;
            }
        }
        $castlesSchema = Application_Model_Board::getCastlesSchema();
        foreach ($castlesSchema as $castleId => $castle)
        {
            if (Application_Model_Database::isCastleRazed($gameId, $castleId, $db)) {
                continue;
            }
            if (Application_Model_Database::isPlayerCastle($gameId, $castleId, $playerId, $db)) {
                $this->fields = Application_Model_Board::changeCasteFields($this->fields, $castle['position']['x'], $castle['position']['y'], 'c');
            } else {
                $this->fields = Application_Model_Board::changeCasteFields($this->fields, $castle['position']['x'], $castle['position']['y'], 'e');
            }
        }

        $aStar = new Game_Astar($destX, $destY);
        try {
            $aStar->start($army['x'], $army['y'], $this->fields, $this->canFly, $this->canSwim);
        } catch (Exception $e) {
            echo($e);
            return;
        }
        $this->path = $aStar->restorePath($destX . '_' . $destY, $army['movesLeft']);
//        throw new Exception(Zend_Debug::dump($path));
        return $aStar->getCurrentPosition();
    }

}

