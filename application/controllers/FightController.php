<?php

class FightController extends Warlords_Controller_Action
{
    private $_result;

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
        $movesSpend = 2;
        $enemyId = $this->_request->getParam('eid');
        if ($armyId !== null AND $x !== null AND $y !== null AND $enemyId !== null) {
            $modelArmy = new Application_Model_Army($this->_namespace->gameId);
            $army = $modelArmy->getArmyByArmyIdPlayerId($armyId, $this->_namespace->player['playerId']);
            if($this->calculateArmiesDistance($x, $y, $army['position']) >= 80) {
                throw new Exception('Wróg znajduje się za daleko aby można go było atakować.');
            }
            $enemies = $modelArmy->getAllArmiesFromPosition(array('x' => $x, 'y' => $y));
            foreach($enemies as $enemy) {
                if($enemy['armyId'] == $enemyId) {
                    $enemyConfirmed = true;
                    break;
                }
            }
            if(!isset($enemyConfirmed)) {
                throw new Exception('Na podanej pozycji nie znaleziono wroga.');
            }
            $result = array('attacker' => $army, 'defender' => null);
            foreach ($enemies as $enemy) {
                $result = $this->battle($result['attacker'], $enemy);
                if (empty($result['attacker']['heroes']) AND empty($result['attacker']['soldiers'])) {
                    $modelArmy->destroyArmy($result['attacker']['armyId'], $this->_namespace->player['playerId']);
                    unset($result['attacker']);
                    if (isset($enemy['armyId'])) {
                        $modelArmy->updateArmyFull($enemy, $result['defender']);
                    }
                    $result = array(
                        'battle' => $this->_result,
                        'victory' => false
                    );
                    $this->view->response = Zend_Json::encode($result);
                    break;
                } elseif (isset($enemy['armyId'])) {
                    $modelArmy->destroyArmy($enemy['armyId'], $enemy['playerId']);
                }
            }
            if (!empty($result['attacker'])) {
                $modelArmy->updateArmyFull($army, $result['attacker']);

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
            }
        } else {
            throw new Exception('Brak "armyId" lub "x" lub "y" lub "movesSpend" lub "$enemyId"!');
        }
    }

    public function castleAction()
    {
        // action body
        $armyId = $this->_request->getParam('armyId');
        $x = $this->_request->getParam('x');
        $y = $this->_request->getParam('y');
        $movesSpend = 2;
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
                if ($army['movesLeft'] < $movesSpend) {
                    throw new Exception('Pozostało mniej ruchów niż gracz próbuje wydać!');
                    return false;
                }
                $modelCastle = new Application_Model_Castle($this->_namespace->gameId);
                $enemies = array();
                $result = array('attacker' => $army, 'defender' => null);
                if ($modelCastle->isEnemyCastle($castleId, $this->_namespace->player['playerId'])) {
                    $enemies = $modelArmy->getAllArmiesFromCastlePosition($castle['position']);
                } else {
                    $enemies[0] = array(
                        'soldiers' => array(
                            array('defensePoints' => 1),
                            array('defensePoints' => 1)
                        ),
                        'heroes' => array()
                    );
                }
                foreach ($enemies as $enemy) {
                    $result = $this->battle($result['attacker'], $enemy);
                    if (empty($result['attacker']['heroes']) AND empty($result['attacker']['soldiers'])) {
                        $modelArmy->destroyArmy($result['attacker']['armyId'], $this->_namespace->player['playerId']);
                        unset($result['attacker']);
                        if (isset($enemy['armyId'])) {
                            $modelArmy->updateArmyFull($enemy, $result['defender']);
                        }
                        $result['defender']['battle'] = $this->_result;
                        $result['defender']['victory'] = false;
                        $this->view->response = Zend_Json::encode($result['defender']);
                        break;
                    } elseif (isset($enemy['armyId'])) {
                        $modelArmy->destroyArmy($enemy['armyId'], $enemy['playerId']);
                    }
                }
                if (!empty($result['attacker'])) {
                    $modelCastle->deleteCastle($castleId);
                    $modelCastle->addCastle($castleId, $this->_namespace->player['playerId']);
                    $modelArmy->updateArmyFull($army, $result['attacker']);

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
                }
            } else {
                throw new Exception('Na podanej pozycji nie ma zamku!');
            }
        } else {
            throw new Exception('Brak "armyId" lub "x" lub "y" lub "movesSpend"!');
        }
    }

    private function battle($attacker, $defender) {
//        throw new Exception(Zend_Debug::dump($attacker, null, false).Zend_Debug::dump($defender, null, false));
        $hits = array('attack' => 2, 'defense' => 2);
        foreach ($attacker['soldiers'] as $a => $unitAttaking) {
//            echo 'i';
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
//            echo 'j';
            foreach ($defender['soldiers'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttaking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($defender['soldiers'][$d]);
                } else {
                    unset($attacker['heroes'][$a]);
                    break;
                }
//                echo 'a';
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
//        throw new Exception(Zend_Debug::dump($attacker['heroes'], null, false).Zend_Debug::dump($defender, null, false));
//        exit;
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
//                echo '$defenseHits-- '.$attackPoints.' > '.$dieDefending.' AND '.$defensePoints.' <= '.$dieAttacking."\n";
            } elseif ($unitAttaking['attackPoints'] <= $dieDefending AND $unitDefending['defensePoints'] > $dieAttacking) {
                $attackHits--;
//                echo '$attackHits-- '.$attackPoints.' <= '.$dieDefending.' AND '.$defensePoints.' > '.$dieAttacking."\n";
            }
            if(isset($unitAttaking['heroId'])) {
                $idA = array('heroId', $unitAttaking['heroId']);
            } else {
                $idA = array('soldierId', $unitAttaking['soldierId']);
            }
            if(isset($unitDefending['heroId'])) {
                $idD = array('heroId', $unitDefending['heroId']);
            } elseif(isset($unitDefending['soldierId'])) {
                $idD = array('soldierId', $unitDefending['soldierId']);
            } else {
                $idD = '?';
            }
            $this->_result[] = array(
                'unitAttaking' => $idA,
                'attackPoints' => $unitAttaking['attackPoints'],
                'dieAttacking' => $dieAttacking,
                'attackHits' => $attackHits,
                'unitDefending' => $idD,
                'defensePoints' => $unitDefending['defensePoints'],
                'dieDefending' => $dieDefending,
                'defenseHits' => $defenseHits
            );
        }
        $this->_result[] = array('attack' => $attackHits, 'defense' => $defenseHits);
        return array('attack' => $attackHits, 'defense' => $defenseHits);
    }

    private function rollDie() {
        return rand(1, 10);
    }

    private function calculateArmiesDistance($x, $y, $position) {
        $position = explode(',', substr($position, 1 , -1));
        return sqrt(pow($x - $position[0], 2) + pow($position[1] - $y, 2));
    }
}

