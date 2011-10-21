<?php

class Game_Battle {

    private $_result = array();
    private $defenseModifier = 0;
    private $attackModifier = 0;
    private $attacker;
    private $defender;

    public function __construct($attacker, $defender) {
        $this->_namespace = new Zend_Session_Namespace();
        if($defender === null){
            $defender = $this->getNeutralCastleGarrizon();
        }
        $this->defender = $this->getCombatModifiers($defender);
        $this->attacker = $this->getCombatModifiers($attacker);
    }

    public function addTowerDefenseModifier($x, $y) {
        if (Application_Model_Board::isTowerAtPosition($x, $y)) {
            $this->defenseModifier += 1;
        }
    }

    public function addCastleDefenseModifier($defenseModifier) {
        $this->defenseModifier += $defenseModifier;
    }

    public function updateArmies() {
        $modelArmy = new Application_Model_Army($this->_namespace->gameId);
        foreach ($this->_result AS $r) {
            if (isset($r['heroId'])) {
                $modelArmy->armyRemoveHero($r['heroId']);
            } else {
                if (strpos($r['soldierId'], 's') === false) {
                    $modelArmy->destroySoldier($r['soldierId']);
                }
            }
        }
    }

    public function getDefender(){
        return $this->defender;
    }

    public function fight() {
//        Zend_Debug::dump($defender);
        $hits = array('attack' => 2, 'defense' => 2);
        foreach ($this->attacker['soldiers'] as $a => $unitAttaking) {
            foreach ($this->defender['soldiers'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttaking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['soldiers'][$d]);
                } else {
                    unset($this->attacker['soldiers'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['soldiers'] as $a => $unitAttaking) {
            foreach ($this->defender['heroes'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttaking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['heroes'][$d]);
                } else {
                    unset($this->attacker['soldiers'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['heroes'] as $a => $unitAttaking) {
            foreach ($this->defender['soldiers'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttaking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['soldiers'][$d]);
                } else {
                    unset($this->attacker['heroes'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['heroes'] as $a => $unitAttaking) {
            foreach ($this->defender['heroes'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttaking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['heroes'][$d]);
                } else {
                    unset($this->attacker['heroes'][$a]);
                    break;
                }
            }
        }
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

    private function getNeutralCastleGarrizon() {
        $modelGame = new Application_Model_Game($this->_namespace->gameId);
        $turn = $modelGame->getTurn();
        $numberOfSoldiers = ceil($turn['nr'] / 10);
        $soldiers = array();
        for ($i = 1; $i <= $numberOfSoldiers; $i++) {
            $soldiers[] = array(
                'defensePoints' => 3,
                'soldierId' => 's' . $i
            );
        }
        return array(
            'soldiers' => $soldiers,
            'heroes' => array()
        );
    }

    private function getCombatModifiers($army) {
        $heroExists = false;
        $canFly = false;
        if (count($army['heroes']) > 0) {
            $heroExists = true;
        }
        foreach ($army['soldiers'] as $soldier) {
            if (isset($soldier['canFly']) && $soldier['canFly']) {
                $canFly = true;
                break;
            }
        }
        if ($canFly) {
            foreach ($army['soldiers'] as $k => $soldier) {
                if ($soldier['canFly']) {
                    continue;
                }
                $army['soldiers'][$k]['attackPoints']++;
                $army['soldiers'][$k]['defensePoints']++;
            }
        }
        if ($heroExists) {
            foreach ($army['soldiers'] as $k => $soldier) {
                $army['soldiers'][$k]['attackPoints']++;
                $army['soldiers'][$k]['defensePoints']++;
            }
        }
        return $army;
    }

}

