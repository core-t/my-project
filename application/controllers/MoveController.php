<?php

class MoveController extends Game_Controller_Action {

    private $canFly = 1;
    private $canSwim = 0;

    public function _init() {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function goAction() {
        // action body
        // sprawdzić czy na pozycji nie ma zamku lub armii wroga!
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        if (!$modelGame->isPlayerTurn($this->_namespace->player['playerId'])) {
            throw new Exception('Nie Twoja tura.');
        }
        $armyId = $this->_request->getParam('aid');
        $x = $this->_request->getParam('x');
        $y = $this->_request->getParam('y');
        if (!empty($armyId) AND $x !== null AND $y !== null) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $army = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
            $this->fields = $modelArmy->getEnemyArmiesFieldsPositions($this->_namespace->player['playerId']);
//             echo '<pre>';print_r($this->fields);echo '</pre>';
            $movesSpend = $this->calculateNewArmyPosition($army, $x/40, $y/40);
            if ($movesSpend > $army['movesLeft']) {
                throw new Exception('Próba wykonania większej ilości ruchów niż jednostka posiada');
            }
            $data = array(
                'position' => $x . ',' . $y,
                'movesSpend' => $movesSpend
            );
            $res = $modelArmy->updateArmyPosition($armyId, $this->_namespace->player['playerId'], $data);
            $armyId = $modelArmy->joinArmiesAtPosition($data['position'], $this->_namespace->player['playerId']);
            switch ($res) {
                case 1:
                    $result = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
                    $result['path'] = $this->path;
                    $this->view->response = Zend_Json::encode($result);
                    break;
                case 0:
                    throw new Exception('Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane');
                    break;
                case null:
                    throw new Exception('Zapytanie zwróciło błąd');
                    break;
                default:
                    throw new Exception('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.');
                    break;
            }
        } else {
            throw new Exception('Brak "armyId" lub "x" lub "y" lub "movesSpend"!');
        }
    }

    private function calculateNewArmyPosition($army, $destX, $destY) {
        foreach ($army['heroes'] as $hero) {
            $this->canFly--;
        }
        foreach ($army['soldiers'] as $soldier) {
            if ($soldier['canFly']) {
                $this->canFly++;
            } else {
                $this->canFly -= 200;
            }
            if ($soldier['canSwim']) {
                $this->canSwim++;
            }
        }
        $position = explode(',', substr($army['position'], 1, -1));
        $position = array('x' => $position[0], 'y' => $position[1]);
        $this->movesLeft = $army['movesLeft'];
        $modelBoard = new Application_Model_Board();

        $castlesSchema = $modelBoard->getCastlesSchema();
        foreach ($castlesSchema as $castle) {
            $y = $castle['position']['y'] / 40;
            $x = $castle['position']['x'] / 40;
            $this->fields[$y][$x] = 'c';
            $this->fields[$y + 1][$x] = 'c';
            $this->fields[$y][$x + 1] = 'c';
            $this->fields[$y + 1][$x + 1] = 'c';
        }

        $aStar = new Game_Astar($position['x'] / 40, $position['y'] / 40, $destX, $destY, $this->fields, $this->canFly, $this->canSwim);
        $key = $destX . '_' . $destY;
        $this->path = $aStar->restorePath($key, $this->movesLeft);
        return $aStar->getMovesSpend();
    }

    private function addPath($pfX, $pfY, $direction, $movesSpend) {
        if ($movesSpend >= $this->movesLeft) {
            return null;
        }
        $terrainType = $this->fields[$pfY][$pfX];
        $terrain = Application_Model_Board::getTerrain($terrainType, $this->canFly, $this->canSwim);
        $this->path[] = array(
            'terrain' => $terrain[0],
            'cost' => $terrain[1],
            'x' => $pfX * 40,
            'y' => $pfY * 40
        );
        if (($movesSpend + $terrain[1]) > $this->movesLeft) {
            return null;
        }
        return $movesSpend + $terrain[1];
    }

}

