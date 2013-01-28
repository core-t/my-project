<?php

class Application_Model_Move {

    private $canFly = 1;
    private $canSwim = 0;

    public function go($gameId, $armyId, $x, $y, $playerId) {
        $army = Application_Model_Database::getArmyByArmyIdPlayerId($gameId, $armyId, $playerId);
        $this->fields = Application_Model_Database::getEnemyArmiesFieldsPositions($gameId, $playerId);

        $currentPosition = $this->calculateNewArmyPosition($gameId, $army, $x, $y, $playerId);

        if (!$currentPosition) {
            echo('Nie wykonano ruchu');
            return;
        }
        if ($currentPosition['movesSpend'] > $army['movesLeft']) {
            echo('Próba wykonania większej ilości ruchów niż jednostka posiada');
            return;
        }
        $res = Application_Model_Database::updateArmyPosition($gameId, $armyId, $playerId, $currentPosition);
        switch ($res)
        {
            case 1:
                $armiesIds = Application_Model_Database::joinArmiesAtPosition($gameId, $currentPosition, $playerId);
                $newArmyId = $armiesIds['armyId'];

                $result = Application_Model_Database::getArmyByArmyIdPlayerId($gameId, $newArmyId, $playerId);
                $result['path'] = $this->path;
                $result['oldArmyId'] = $armyId;
                $result['deletedIds'] = $armiesIds;
                return $result;
                break;
            case 0:
                echo('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                break;
            case null:
                echo('Zapytanie zwróciło błąd');
                break;
            default:
                echo('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                break;
        }
    }

    private function calculateNewArmyPosition($gameId, $army, $destX, $destY, $playerId) {
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
            if (Application_Model_Database::isCastleRazed($gameId, $castleId)) {
                continue;
            }
            if (!Application_Model_Database::isPlayerCastle($gameId, $castleId, $playerId)) {
                $this->fields = Application_Model_Board::changeCasteFields($this->fields, $castle['position']['x'], $castle['position']['y'], 'e');
            } else {
                $this->fields = Application_Model_Board::changeCasteFields($this->fields, $castle['position']['x'], $castle['position']['y'], 'c');
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

