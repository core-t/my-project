<?php

class Cli_Model_Battle
{

    private $_result = array(
        'defense' => array(
            'heroes' => array(),
            'soldiers' => array(),
        ),
        'attack' => array(
            'heroes' => array(),
            'soldiers' => array(),
        ),
    );
    private $attacker;
    private $defender;
    private $succession = 0;

    public function __construct($attacker, $defender)
    {
        $this->defender = $defender;
        $this->attacker = $attacker;
    }

    public function updateArmies($gameId, $db)
    {
        $this->updateHeroes($this->_result['defense']['heroes'], $gameId, $db);
        $this->updateSoldiers($this->_result['defense']['soldiers'], $gameId, $db);
        $this->updateHeroes($this->_result['attack']['heroes'], $gameId, $db);
        $this->updateSoldiers($this->_result['attack']['soldiers'], $gameId, $db);
    }

    private function updateHeroes($heroes, $gameId, $db)
    {
        foreach ($heroes as $v) {
            Cli_Model_Database::armyRemoveHero($gameId, $v['heroId'], $db);
        }
    }

    private function updateSoldiers($soldiers, $gameId, $db)
    {
        foreach ($soldiers as $v) {
            if (strpos($v['soldierId'], 's') === false) {
                Cli_Model_Database::destroySoldier($gameId, $v['soldierId'], $db);
            }
        }
    }

    public function getDefender()
    {
        if (empty($this->defender['soldiers']) && empty($this->defender['heroes'])) {
            return null;
        }
        return $this->defender;
    }

    public function getAttacker()
    {
        if (!empty($this->attacker['soldiers']) || !empty($this->attacker['heroes'])) {
            return $this->attacker;
        }
    }

    public function fight()
    {
        $units = Zend_Registry::get('units');
        $hits = array('attack' => 2, 'defense' => 2);
        foreach ($this->attacker['soldiers'] as $a => $unitAttacking) {
            $unitAttacking['attackPoints'] = $units[$unitAttacking['unitId']]['attackPoints'];
            foreach ($this->defender['soldiers'] as $d => $unitDefending) {
                $unitDefending['defensePoints'] = $units[$unitDefending['unitId']]['defensePoints'];
                $hits = $this->combat($unitAttacking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['soldiers'][$d]);
                } else {
                    unset($this->attacker['soldiers'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['soldiers'] as $a => $unitAttacking) {
            $unitAttacking['attackPoints'] = $units[$unitAttacking['unitId']]['attackPoints'];
            foreach ($this->defender['heroes'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttacking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['heroes'][$d]);
                } else {
                    unset($this->attacker['soldiers'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['heroes'] as $a => $unitAttacking) {
            foreach ($this->defender['soldiers'] as $d => $unitDefending) {
                $unitDefending['defensePoints'] = $units[$unitDefending['unitId']]['defensePoints'];
                $hits = $this->combat($unitAttacking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['soldiers'][$d]);
                } else {
                    unset($this->attacker['heroes'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['heroes'] as $a => $unitAttacking) {
            foreach ($this->defender['heroes'] as $d => $unitDefending) {
                $hits = $this->combat($unitAttacking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['heroes'][$d]);
                } else {
                    unset($this->attacker['heroes'][$a]);
                    break;
                }
            }
        }
    }

    private function combat($unitAttacking, $unitDefending, $hits)
    {
        $attackHits = $hits['attack'];
        $defenseHits = $hits['defense'];

        if (!$attackHits) {
            $attackHits = 2;
        }

        if (!$defenseHits) {
            $defenseHits = 2;
        }

        $unitAttacking['attackPoints'] += $this->attacker['attackModifier'];

        if (isset($this->defender['defenseModifier'])) {
            $unitDefending['defensePoints'] += $this->defender['defenseModifier'];
        }

        while ($attackHits AND $defenseHits) {
            $maxDie = $unitAttacking['attackPoints'] + $unitDefending['defensePoints'];
            $dieAttacking = $this->rollDie($maxDie);
            $dieDefending = $this->rollDie($maxDie);
//            $dieAttacking = $this->rollDie($unitAttacking['attackPoints']);
//            $dieDefending = $this->rollDie($unitDefending['defensePoints']);

//            echo '$unitAttacking[\'attackPoints\']=' . $unitAttacking['attackPoints'] . "\n";
//            echo '$dieDefending=' . $dieDefending . "\n";
//            echo '$unitDefending[\'defensePoints\']=' . $unitDefending['defensePoints'] . "\n";
//            echo '$dieAttacking=' . $dieAttacking . "\n\n";

            if ($unitAttacking['attackPoints'] > $dieDefending AND $unitDefending['defensePoints'] <= $dieAttacking) {
                $defenseHits--;
            } elseif ($unitAttacking['attackPoints'] <= $dieDefending AND $unitDefending['defensePoints'] > $dieAttacking) {
                $attackHits--;
            }
        }

        $this->succession++;

        if ($attackHits) {
            if (isset($unitDefending['heroId'])) {
                $this->_result['defense']['heroes'][] = array(
                    'heroId' => $unitDefending['heroId'],
                    'succession' => $this->succession
                );
            } else {
                $this->_result['defense']['soldiers'][] = array(
                    'soldierId' => $unitDefending['soldierId'],
                    'succession' => $this->succession
                );
            }
        } else {
            if (isset($unitAttacking['heroId'])) {
                $this->_result['attack']['heroes'][] = array(
                    'heroId' => $unitAttacking['heroId'],
                    'succession' => $this->succession
                );
            } else {
                $this->_result['attack']['soldiers'][] = array(
                    'soldierId' => $unitAttacking['soldierId'],
                    'succession' => $this->succession
                );
            }
        }

        return array('attack' => $attackHits, 'defense' => $defenseHits);
    }

    private function rollDie($maxDie)
    {
        return rand(1, $maxDie);
    }

    public function getResult($army, $enemy)
    {
        $battle = array(
            'defense' => array(
                'heroes' => array(),
                'soldiers' => array(),
            ),
            'attack' => array(
                'heroes' => array(),
                'soldiers' => array(),
            ),
        );
        foreach ($enemy['heroes'] as $unit) {
            $succession = null;
            foreach ($this->_result['defense']['heroes'] as $battleUnit) {
                if ($battleUnit['heroId'] == $unit['heroId']) {
                    $succession = $battleUnit['succession'];
                }
            }
            $battle['defense']['heroes'][] = array(
                'heroId' => $unit['heroId'],
                'succession' => $succession
            );
        }
        foreach ($enemy['soldiers'] as $unit) {
            $succession = null;
            foreach ($this->_result['defense']['soldiers'] as $battleUnit) {
                if ($battleUnit['soldierId'] == $unit['soldierId']) {
                    $succession = $battleUnit['succession'];
                }
            }
            $battle['defense']['soldiers'][] = array(
                'soldierId' => $unit['soldierId'],
                'succession' => $succession,
                'unitId' => $unit['unitId'],
            );
        }
        foreach ($army['heroes'] as $unit) {
            $succession = null;
            foreach ($this->_result['attack']['heroes'] as $battleUnit) {
                if ($battleUnit['heroId'] == $unit['heroId']) {
                    $succession = $battleUnit['succession'];
                }
            }
            $battle['attack']['heroes'][] = array(
                'heroId' => $unit['heroId'],
                'succession' => $succession,
            );
        }
        foreach ($army['soldiers'] as $unit) {
            $succession = null;
            foreach ($this->_result['attack']['soldiers'] as $battleUnit) {
                if ($battleUnit['soldierId'] == $unit['soldierId']) {
                    $succession = $battleUnit['succession'];
                }
            }
            $battle['attack']['soldiers'][] = array(
                'soldierId' => $unit['soldierId'],
                'succession' => $succession,
                'unitId' => $unit['unitId'],
            );
        }

        return $battle;
    }

    static public function getNeutralCastleGarrison($gameId, $db)
    {
        $turn = Cli_Model_Database::getTurn($gameId, $db);
        $numberOfSoldiers = ceil($turn['nr'] / 10);
        $soldiers = array();
        for ($i = 1; $i <= $numberOfSoldiers; $i++) {
            $soldiers[] = array(
                'defensePoints' => 3,
                'soldierId' => 's' . $i,
                'unitId' => 1
            );
        }
        return array(
            'soldiers' => $soldiers,
            'heroes' => array(),
            'ids' => array(),
            'defenseModifier' => 0
        );
    }

//    public function setCombatAttackModifiers($army) {
//        if ($army['heroes']) {
//            echo '
//                hero!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//                ';
//            $this->attackModifier++;
//        }
//        if ($army['canFly'] > 0) {
//            $this->attackModifier++;
//        }
//    }

}

