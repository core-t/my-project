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
            $currentPosition = $this->calculateNewArmyPosition($army, $x / 40, $y / 40);
//            throw new Exception(Zend_Debug::dump($currentPosition));
            if (!$currentPosition) {
                throw new Exception('Nie wykonano ruchu');
            }
            if ($currentPosition['movesSpend'] > $army['movesLeft']) {
                throw new Exception('Próba wykonania większej ilości ruchów niż jednostka posiada');
            }
            $data = array(
                'position' => $currentPosition['x'] . ',' . $currentPosition['y'],
                'movesSpend' => $currentPosition['movesSpend']
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
        $modelBoard = new Application_Model_Board();
        $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
        $castlesSchema = $modelBoard->getCastlesSchema();
        foreach ($castlesSchema as $castleId => $castle) {
            if($modelCastle->isCastleRazed($castleId)){
                continue;
            }
            $y = $castle['position']['y'] / 40;
            $x = $castle['position']['x'] / 40;
            if (!$modelCastle->isPlayerCastle($castleId, $this->_namespace->player['playerId'])) {
                $this->fields = Application_Model_Board::changeCasteFields($this->fields, $x, $y, 'e');
            } else {
                $this->fields = Application_Model_Board::changeCasteFields($this->fields, $x, $y, 'c');
            }
        }

        $aStar = new Game_Astar($destX, $destY);
        $aStar->start($position['x'] / 40, $position['y'] / 40, $this->fields, $this->canFly, $this->canSwim);
        $this->path = $aStar->restorePath($destX . '_' . $destY, $army['movesLeft']);
//        throw new Exception(Zend_Debug::dump($path));
        return $aStar->getCurrentPosition();
    }

}

