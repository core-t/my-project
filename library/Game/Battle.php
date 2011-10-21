<?php

class Game_Battle {

    private $_result = array();
    private $defenseModifier = 0;
    private $attackModifier = 0;

    public function __construct() {

    }

    public function addTowerDefenseModifier($x, $y) {
        if (Application_Model_Board::isTowerAtPosition($x, $y)) {
            $this->defenseModifier += 1;
        }
    }

    public function addCastleDefenseModifier($defenseModifier){
        $this->defenseModifier += $defenseModifier;
    }

    public function fight($attacker, $defender) {
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
        }
        foreach ($attacker['soldiers'] as $a => $unitAttaking) {
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
        }
        foreach ($attacker['heroes'] as $a => $unitAttaking) {
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
        return $defender;
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
        $unitAttaking['attackPoints'] += $this->attackModifier;
        $unitDefending['defensePoints'] += $this->defenseModifier;
        while ($attackHits AND $defenseHits) {
            $maxDie = $unitAttaking['attackPoints'] + $unitDefending['defensePoints'];
            $dieAttacking = $this->rollDie($maxDie);
            $dieDefending = $this->rollDie($maxDie);
            if (isset($unitAttaking['heroId'])) {
                $id = array('heroId', $unitAttaking['heroId']);
            } else {
                $id = array('soldierId', $unitAttaking['soldierId']);
            }
            if ($unitAttaking['attackPoints'] > $dieDefending AND $unitDefending['defensePoints'] <= $dieAttacking) {
                $defenseHits--;
            } elseif ($unitAttaking['attackPoints'] <= $dieDefending AND $unitDefending['defensePoints'] > $dieAttacking) {
                $attackHits--;
            }
            if (isset($unitAttaking['heroId'])) {
                $idA = array('heroId' => $unitAttaking['heroId']);
            } else {
                $idA = array('soldierId' => $unitAttaking['soldierId']);
            }
            if (isset($unitDefending['heroId'])) {
                $idD = array('heroId' => $unitDefending['heroId']);
            } else {
                $idD = array('soldierId' => $unitDefending['soldierId']);
            }
        }
        if ($attackHits) {
            $this->_result[] = $idD;
        } else {
            $this->_result[] = $idA;
        }
        return array('attack' => $attackHits, 'defense' => $defenseHits);
    }

    private function rollDie($maxDie) {
        return rand(1, $maxDie);
    }

    public function getResult() {
        return $this->_result;
    }

}

