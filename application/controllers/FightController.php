<?php

class FightController extends Game_Controller_Action {

    public function _init() {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function armyAction() {
        // action body
        $armyId = $this->_request->getParam('armyId');
        $x = $this->_request->getParam('x');
        $y = $this->_request->getParam('y');
        $enemyId = $this->_request->getParam('eid');
        if ($armyId !== null AND $x !== null AND $y !== null AND $enemyId !== null) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $army = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
            if ($this->calculateArmiesDistance($x, $y, $army['position']) >= 80) {
                throw new Exception('Wróg znajduje się za daleko aby można go było atakować.');
            }
            if (($movesSpend = $this->movesSpend($x, $y, $army, 1)) > $army['movesLeft']) {
                throw new Exception('Armia ma za mało ruchów do wykonania akcji (' . $movesSpend . '>' . $army['movesLeft'] . ').');
            }
            $enemy = $modelArmy->getAllUnitsFromPosition(array('x' => $x, 'y' => $y));
            $battle = new Game_Battle($army, $enemy);
            $battle->addTowerDefenseModifier($x, $y);
            $battle->fight();
            $battle->updateArmies();
            $enemy = $modelArmy->updateAllArmiesFromPosition(array('x' => $x, 'y' => $y));
            if (empty($enemy)) {
                $data = array(
                    'position' => $x . ',' . $y,
                    'movesSpend' => $movesSpend
                );
                $res = $modelArmy->updateArmyPosition($armyId, $this->_namespace->player['playerId'], $data);
                switch ($res) {
                    case 1:
                        $result = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
                        $result['victory'] = true;
                        $result['battle'] = $battle->getResult();
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
                $modelArmy->destroyArmy($army['armyId'], $this->_namespace->player['playerId']);
                $enemy['battle'] = $battle->getResult();
                $enemy['victory'] = false;
                $this->view->response = Zend_Json::encode($enemy);
            }
        } else {
            throw new Exception('Brak "armyId" lub "x" lub "y" lub "$enemyId"!');
        }
    }

    public function ecastleAction() {
        // action body
        $armyId = $this->_request->getParam('armyId');
        $x = $this->_request->getParam('x');
        $y = $this->_request->getParam('y');
        $castleId = $this->_request->getParam('cid');
        if ($armyId !== null AND $x !== null AND $y !== null AND $castleId !== null) {
            $modelBoard = new Application_Model_Board();
            $castle = $modelBoard->getCastle($castleId);
            if (empty($castle)) {
                throw new Exception('Brak zamku o podanym ID!');
                return false;
            }
            if (($x >= $castle['position']['x']) AND ($x < ($castle['position']['x'] + 80)) AND ($y >= $castle['position']['y']) AND ($y < ($castle['position']['y'] + 80))) {
                $modelArmy = new Application_Model_Army($this->_namespace->gameId);
                $army = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
                if (empty($army)) {
                    throw new Exception('Brak armii o podanym ID!');
                    return false;
                }
                if ($this->calculateArmiesDistance($x, $y, $army['position']) >= 80) {
                    throw new Exception('Wróg znajduje się za daleko aby można go było atakować.');
                }
                if (($movesSpend = 2) > $army['movesLeft']) {
                    throw new Exception('Armia ma za mało ruchów do wykonania akcji(' . $movesSpend . '>' . $army['movesLeft'] . ').');
                }
                $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
                if ($modelCastle->isEnemyCastle($castleId, $this->_namespace->player['playerId'])) {
                    $enemy = $modelArmy->getAllUnitsFromCastlePosition($castle['position']);
                    $battle = new Game_Battle($army, $enemy);
                    $battle->addCastleDefenseModifier($castle['defensePoints'] + $modelCastle->getCastleDefenseModifier($castleId));
                    $battle->fight();
                    $battle->updateArmies();
                    $enemy = $modelArmy->updateAllArmiesFromCastlePosition($castle['position']);
                } else {
                    throw new Exception('To nie jest zamek wroga.');
                }
                if (empty($enemy)) {
                    $res = $modelCastle->changeOwner($castleId, $this->_namespace->player['playerId']);
                    if ($res == 1) {
                        $data = array(
                            'position' => $x . ',' . $y,
                            'movesSpend' => $movesSpend
                        );
                        $res = $modelArmy->updateArmyPosition($armyId, $this->_namespace->player['playerId'], $data);
                        switch ($res) {
                            case 1:
                                $result = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
                                $result['victory'] = true;
                                $result['battle'] = $battle->getResult();
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
                        throw new Exception('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.' . $res);
                    }
                } else {
                    $modelArmy->destroyArmy($army['armyId'], $this->_namespace->player['playerId']);
                    $enemy['battle'] = $battle->getResult();
                    $enemy['victory'] = false;
                    $this->view->response = Zend_Json::encode($enemy);
                }
            } else {
                throw new Exception('Na podanej pozycji nie ma zamku!');
            }
        } else {
            throw new Exception('Brak "armyId" lub "x" lub "y"!');
        }
    }

    public function ncastleAction() {
        // action body
        $armyId = $this->_request->getParam('armyId');
        $x = $this->_request->getParam('x');
        $y = $this->_request->getParam('y');
        $castleId = $this->_request->getParam('cid');
        if ($armyId !== null AND $x !== null AND $y !== null AND $castleId !== null) {
            $modelBoard = new Application_Model_Board();
            $castle = $modelBoard->getCastle($castleId);
            if (empty($castle)) {
                throw new Exception('Brak zamku o podanym ID!');
                return false;
            }
            if (($x >= $castle['position']['x']) AND ($x < ($castle['position']['x'] + 80)) AND ($y >= $castle['position']['y']) AND ($y < ($castle['position']['y'] + 80))) {
                $modelArmy = new Application_Model_Army($this->_namespace->gameId);
                $army = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
                if (empty($army)) {
                    throw new Exception('Brak armii o podanym ID!');
                    return false;
                }
                if ($this->calculateArmiesDistance($x, $y, $army['position']) >= 80) {
                    throw new Exception('Wróg znajduje się za daleko aby można go było atakować.');
                }
                $movesSpend = 2;
                if ($movesSpend > $army['movesLeft']) {
                    throw new Exception('Armia ma za mało ruchów do wykonania akcji(' . $movesSpend . '>' . $army['movesLeft'] . ').');
                }
                $battle = new Game_Battle($army, null);
                $battle->fight();
                $battle->updateArmies();
                $defender = $battle->getDefender();

                if (empty($defender['soldiers'])) {
                    $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
                    $res = $modelCastle->addCastle($castleId, $this->_namespace->player['playerId']);
                    if ($res == 1) {
                        $data = array(
                            'position' => $x . ',' . $y,
                            'movesSpend' => $movesSpend
                        );
                        $res = $modelArmy->updateArmyPosition($armyId, $this->_namespace->player['playerId'], $data);
                        switch ($res) {
                            case 1:
                                $result = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
                                $result['victory'] = true;
                                $result['battle'] = $battle->getResult();
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
                        throw new Exception('Nieznany błąd. Możliwe, że został zaktualizowany więcej niż jeden rekord.' . $res);
                    }
                } else {
                    $modelArmy->destroyArmy($army['armyId'], $this->_namespace->player['playerId']);
                    $defender['battle'] = $battle->getResult();
                    $defender['victory'] = false;
                    $this->view->response = Zend_Json::encode($defender);
                }
            } else {
                throw new Exception('Na podanej pozycji nie ma zamku!');
            }
        } else {
            throw new Exception('Brak "armyId" lub "x" lub "y"!');
        }
    }

    private function calculateArmiesDistance($x, $y, $position) {
        $position = explode(',', substr($position, 1, -1));
        return sqrt(pow($x - $position[0], 2) + pow($position[1] - $y, 2));
    }

    private function movesSpend($x, $y, $army) {
        $canFly = 1;
        $canSwim = 0;
        $movesRequiredToAttack = 1;
        foreach ($army['heroes'] as $hero) {
            $canFly--;
        }
        foreach ($army['soldiers'] as $soldier) {
            if ($soldier['canFly']) {
                $canFly++;
            } else {
                $canFly -= 200;
            }
            if ($soldier['canSwim']) {
                $canSwim++;
            }
        }
        $fields = Application_Model_Board::getBoardFields();
        $terrainType = $fields[$y / 40][$x / 40];
        $terrain = Application_Model_Board::getTerrain($terrainType, $canFly, $canSwim);
        return $terrain[1] + $movesRequiredToAttack;
    }


}

