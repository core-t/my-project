<?php

class FightController extends Warlords_Controller_Action
{
    private $_result = array();
    private $_movesRequiredToAttack = 1;

    public function _init()
    {
        /* Initialize action controller here */
        $this->_helper->layout->disableLayout();
        if (empty($this->_namespace->gameId)) {
            throw new Exception('Brak "gameId"!');
        }
    }

    public function armyAction()
    {
        // action body
        $armyId = $this->_request->getParam('armyId');
        $x = $this->_request->getParam('x');
        $y = $this->_request->getParam('y');
        $enemyId = $this->_request->getParam('eid');
        if ($armyId !== null AND $x !== null AND $y !== null AND $enemyId !== null) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $army = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
            if($this->calculateArmiesDistance($x, $y, $army['position']) >= 80) {
                throw new Exception('Wróg znajduje się za daleko aby można go było atakować.');
            }
            if(($movesSpend = $this->movesSpend($x, $y)) > $army['movesLeft']) {
                throw new Exception('Armia ma za mało ruchów do wykonania akcji ('.$movesSpend.'>'.$army['movesLeft'].').');
            }
            $enemy = $modelArmy->getAllUnitsFromPosition(array('x' => $x, 'y' => $y));
            $this->battle($army, $enemy);
            foreach($this->_result AS $r) {
                if(isset($r['heroId'])) {
                    $modelArmy->armyRemoveHero($r['heroId']);
                } else {
                    $modelArmy->destroySoldier($r['soldierId']);
                }
            }
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
                    $result['battle'] = $this->_result;
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
                $enemy['battle'] = $this->_result;
                $enemy['victory'] = false;
                $this->view->response = Zend_Json::encode($enemy);
            }
        } else {
            throw new Exception('Brak "armyId" lub "x" lub "y" lub "$enemyId"!');
        }
    }

    public function castleAction()
    {
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
                if($this->calculateArmiesDistance($x, $y, $army['position']) >= 80) {
                    throw new Exception('Wróg znajduje się za daleko aby można go było atakować.');
                }
                if(($movesSpend = $this->movesSpend($x, $y)) > $army['movesLeft']) {
                    throw new Exception('Armia ma za mało ruchów do wykonania akcji('.$movesSpend.'>'.$army['movesLeft'].').');
                }
                $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
                if ($modelCastle->isEnemyCastle($castleId, $this->_namespace->player['playerId'])) {
                    $enemy = $modelArmy->getAllUnitsFromCastlePosition($castle['position']);
                } else {
                    $enemy = array(
                        'soldiers' => array(
                            array(
                                'defensePoints' => 3,
                                'soldierId' => 's1'
                                ),
                            array(
                                'defensePoints' => 3,
                                'soldierId' => 's2'
                                ),
                            array(
                                'defensePoints' => 3,
                                'soldierId' => 's3'
                                )
                        ),
                        'heroes' => array()
                    );
                }
                $this->battle($army, $enemy);
                foreach($this->_result AS $r) {
                    if(isset($r['heroId'])) {
                        $modelArmy->armyRemoveHero($r['heroId']);
                    } else {
                        if(strpos($r['soldierId'],'s') === false){
                            $modelArmy->destroySoldier($r['soldierId']);
                        }
                    }
                }
                $enemy = $modelArmy->updateAllArmiesFromCastlePosition($castle['position']);
                if (empty($enemy)) {
                    $modelCastle->deleteCastle($castleId);
                    $modelCastle->addCastle($castleId, $this->_namespace->player['playerId']);
                    $data = array(
                        'position' => $x . ',' . $y,
                        'movesSpend' => $movesSpend
                    );
                    $res = $modelArmy->updateArmyPosition($armyId, $this->_namespace->player['playerId'], $data);
                    switch ($res) {
                        case 1:
                        $result = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
                        $result['victory'] = true;
                        $result['battle'] = $this->_result;
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
                    $enemy['battle'] = $this->_result;
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

    private function battle($attacker, $defender) {
//        Zend_Debug::dump($defender);
        $hits = array('attack' => 2, 'defense' => 2);
        foreach ($attacker['soldiers'] as $a => $unitAttaking) {
            foreach ($defender['soldiers'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttaking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($defender['soldiers'][$d]);
                } else {
                    unset($attacker['soldiers'][$a]);
                    break;
                }
            }
            foreach ($defender['heroes'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttaking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($defender['heroes'][$d]);
                } else {
                    unset($attacker['soldiers'][$a]);
                    break;
                }
            }
        }
        foreach ($attacker['heroes'] as $a => $unitAttaking) {
            foreach ($defender['soldiers'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttaking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($defender['soldiers'][$d]);
                } else {
                    unset($attacker['heroes'][$a]);
                    break;
                }
            }
            foreach ($defender['heroes'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttaking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($defender['heroes'][$d]);
                } else {
                    unset($attacker['heroes'][$a]);
                    break;
                }
            }
        }
        return array('attacker' => $attacker, 'defender' => $defender);
    }

    private function combat($unitAttaking, $unitDefending, $hits) {
        $attackHits = $hits['attack'];
        $defenseHits = $hits['defense'];
        if (!$attackHits) {
            $attackHits = 2;
        }
        if (!$defenseHits) {
            $defenseHits = 2;
        }
        while ($attackHits AND $defenseHits) {
            $dieAttacking = $this->rollDie();
            $dieDefending = $this->rollDie();
            if(isset($unitAttaking['heroId'])) {
                $id = array('heroId', $unitAttaking['heroId']);
            } else {
                $id = array('soldierId', $unitAttaking['soldierId']);
            }
            if ($unitAttaking['attackPoints'] > $dieDefending AND $unitDefending['defensePoints'] <= $dieAttacking) {
                $defenseHits--;
            } elseif ($unitAttaking['attackPoints'] <= $dieDefending AND $unitDefending['defensePoints'] > $dieAttacking) {
                $attackHits--;
            }
            if(isset($unitAttaking['heroId'])) {
                $idA = array('heroId' => $unitAttaking['heroId']);
            } else {
                $idA = array('soldierId' => $unitAttaking['soldierId']);
            }
            if(isset($unitDefending['heroId'])) {
                $idD = array('heroId' => $unitDefending['heroId']);
            } else {
                $idD = array('soldierId' => $unitDefending['soldierId']);
            }
        }
        if($attackHits){
            $this->_result[] = $idD;
        } else {
            $this->_result[] = $idA;
        }
        return array('attack' => $attackHits, 'defense' => $defenseHits);
    }

    private function rollDie() {
        return rand(1, 10);
    }

    private function calculateArmiesDistance($x, $y, $position) {
        $position = explode(',', substr($position, 1 , -1));
        return sqrt(pow($x - $position[0], 2) + pow($position[1] - $y, 2));
    }

    private function movesSpend($x, $y) {
        $modelBoard = new Application_Model_Board();
        $fields = Application_Model_Board::getBoardFields();
        $castlesSchema = $modelBoard->getCastlesSchema();
        foreach($castlesSchema as $castle) {
            $cy = $castle['position']['y']/40;
            $cx = $castle['position']['x']/40;
            $fields[$cy][$cx] = 'r';
            $fields[$cy + 1][$cx + 1] = 'r';
        }
        $terrainType = $fields[$y/40][$x/40];
        $terrain = Application_Model_Board::getTerrain($terrainType);
        return $terrain[1] + $this->_movesRequiredToAttack;
    }
}

