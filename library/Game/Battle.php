<?php

class Game_Battle {

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
    private $defenseModifier = 0;
    private $attackModifier = 0;
    private $attacker;
    private $defender;
    private $succession = 0;

    public function __construct($attacker, $defender) {
        $this->defender = $this->getCombatModifiers($defender);
        $this->attacker = $this->getCombatModifiers($attacker);
    }

    public function addTowerDefenseModifier($x, $y) {
        if (Application_Model_Board::isTowerAtPosition($x, $y)) {
            $this->defenseModifier += 1;
        }
    }

    public function addCastleDefenseModifier($gameId, $castleId, $db = null) {
        if (!$db) {
            Application_Model_Database::getDb();
        }
        $defenseModifier = Application_Model_Board::getCastleDefense($castleId) + Application_Model_Database::getCastleDefenseModifier($gameId, $castleId, $db);
        if ($defenseModifier > 0) {
            $this->defenseModifier += $defenseModifier;
        }
    }

    public function updateArmies($gameId, $db = null) {
        $this->updateHeroes($this->_result['defense']['heroes'], $gameId, $db);
        $this->updateSoldiers($this->_result['defense']['soldiers'], $gameId, $db);
        $this->updateHeroes($this->_result['attack']['heroes'], $gameId, $db);
        $this->updateSoldiers($this->_result['attack']['soldiers'], $gameId, $db);
    }

    private function updateHeroes($heroes, $gameId, $db) {
        foreach ($heroes as $v)
        {
            Application_Model_Database::armyRemoveHero($gameId, $v['heroId'], $db);
        }
    }

    private function updateSoldiers($soldiers, $gameId, $db) {
        foreach ($soldiers as $v)
        {
            if (strpos($v['soldierId'], 's') === false) {
                Application_Model_Database::destroySoldier($gameId, $v['soldierId'], $db);
            }
        }
    }

    public function getDefender() {
        return $this->defender;
    }

    public function getAttacker() {
        if (!empty($this->attacker['soldiers']) || !empty($this->attacker['heroes'])) {
//             new Game_Logger($this->attacker);
            return $this->attacker;
        }
    }

    public function fight() {
//        Zend_Debug::dump($defender);
        $hits = array('attack' => 2, 'defense' => 2);
        foreach ($this->attacker['soldiers'] as $a => $unitAttaking)
        {
            foreach ($this->defender['soldiers'] as $d => $unitDefending)
            {
                $hits = $this->combat($unitAttaking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['soldiers'][$d]);
                } else {
                    unset($this->attacker['soldiers'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['soldiers'] as $a => $unitAttaking)
        {
            foreach ($this->defender['heroes'] as $d => $unitDefending)
            {
                $hits = $this->combat($unitAttaking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['heroes'][$d]);
                } else {
                    unset($this->attacker['soldiers'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['heroes'] as $a => $unitAttaking)
        {
            foreach ($this->defender['soldiers'] as $d => $unitDefending)
            {
                $hits = $this->combat($unitAttaking, $unitDefending, $hits);
                if ($hits['attack'] > $hits['defense']) {
                    unset($this->defender['soldiers'][$d]);
                } else {
                    unset($this->attacker['heroes'][$a]);
                    break;
                }
            }
        }
        foreach ($this->attacker['heroes'] as $a => $unitAttaking)
        {
            foreach ($this->defender['heroes'] as $d => $unitDefending)
            {
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

    private function combat($unitAttacking, $unitDefending, $hits) {
        $attackHits = $hits['attack'];
        $defenseHits = $hits['defense'];

        if (!$attackHits) {
            $attackHits = 2;
        }

        if (!$defenseHits) {
            $defenseHits = 2;
        }

        $unitAttacking['attackPoints'] += $this->attackModifier;
        $unitDefending['defensePoints'] += $this->defenseModifier;

        while ($attackHits AND $defenseHits)
        {
            $maxDie = $unitAttacking['attackPoints'] + $unitDefending['defensePoints'];
            $dieAttacking = $this->rollDie($maxDie);
            $dieDefending = $this->rollDie($maxDie);

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

    private function rollDie($maxDie) {
        return rand(1, $maxDie);
    }

    public function getResult($army, $enemy) {
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
        foreach ($enemy['heroes'] as $unit)
        {
            $succession = null;
            foreach ($this->_result['defense']['heroes'] as $battleUnit)
            {
                if ($battleUnit['heroId'] == $unit['heroId']) {
                    $succession = $battleUnit['succession'];
                }
            }
            $battle['defense']['heroes'][] = array(
                'heroId' => $unit['heroId'],
                'succession' => $succession
            );
        }
        foreach ($enemy['soldiers'] as $unit)
        {
            $succession = null;
            foreach ($this->_result['defense']['soldiers'] as $battleUnit)
            {
                if ($battleUnit['soldierId'] == $unit['soldierId']) {
                    $succession = $battleUnit['succession'];
                }
            }
            $battle['defense']['soldiers'][] = array(
                'soldierId' => $unit['soldierId'],
                'succession' => $succession,
                'name' => $unit['name'],
            );
        }
        foreach ($army['heroes'] as $unit)
        {
            $succession = null;
            foreach ($this->_result['attack']['heroes'] as $battleUnit)
            {
                if ($battleUnit['heroId'] == $unit['heroId']) {
                    $succession = $battleUnit['succession'];
                }
            }
            $battle['attack']['heroes'][] = array(
                'heroId' => $unit['heroId'],
                'succession' => $succession,
            );
        }
        foreach ($army['soldiers'] as $unit)
        {
            $succession = null;
            foreach ($this->_result['attack']['soldiers'] as $battleUnit)
            {
                if ($battleUnit['soldierId'] == $unit['soldierId']) {
                    $succession = $battleUnit['succession'];
                }
            }
            $battle['attack']['soldiers'][] = array(
                'soldierId' => $unit['soldierId'],
                'succession' => $succession,
                'name' => $unit['name'],
            );
        }

        return $battle;
    }

    static public function getNeutralCastleGarrizon($gameId) {
        $turn = Application_Model_Database::getTurn($gameId);
        $numberOfSoldiers = ceil($turn['nr'] / 10);
        $soldiers = array();
        for ($i = 1; $i <= $numberOfSoldiers; $i++)
        {
            $soldiers[] = array(
                'defensePoints' => 3,
                'soldierId' => 's' . $i,
                'name' => 'Light Infantry'
            );
        }
        return array(
            'soldiers' => $soldiers,
            'heroes' => array()
        );
    }

    static private function getCombatModifiers($army) {
        $heroExists = false;
        $canFly = false;
        if (count($army['heroes']) > 0) {
            $heroExists = true;
        }
        foreach ($army['soldiers'] as $soldier)
        {
            if (isset($soldier['canFly']) && $soldier['canFly']) {
                $canFly = true;
                break;
            }
        }
        if ($canFly) {
            foreach ($army['soldiers'] as $k => $soldier)
            {
                if ($soldier['canFly']) {
                    continue;
                }
                $army['soldiers'][$k]['attackPoints']++;
                $army['soldiers'][$k]['defensePoints']++;
            }
        }
        if ($heroExists) {
            foreach ($army['soldiers'] as $k => $soldier)
            {
                $army['soldiers'][$k]['attackPoints']++;
                $army['soldiers'][$k]['defensePoints']++;
            }
        }
        return $army;
    }

//     static public function getCastlePower($castleId, $playerId) {
//         $namespace = Game_Namespace::getNamespace();
//         $modelArmy = new Application_Model_Army($namespace->gameId);
//         $modelCastle = new Application_Model_Castle($namespace->gameId);
//         $power = 0;
//         if ($modelCastle->isEnemyCastle($castleId, $playerId)) {
//             $enemy = $modelArmy->getAllUnitsFromCastlePosition(Application_Model_Board::getCastlePosition($castleId));
//             $castleDefenseModifier = self::getCastleDefenseModifier($castleId);
//         } else {
//             $enemy = self::getNeutralCastleGarrizon();
//             $castleDefenseModifier = 0;
//         }
//         $enemy = self::getCombatModifiers($enemy);
//         foreach ($enemy['soldiers'] as $soldier) {
//             $power += $soldier['defensePoints'] + $castleDefenseModifier;
//         }
//         foreach ($enemy['heroes'] as $hero) {
//             $power += $hero['defensePoints'] + $castleDefenseModifier;
//         }
//         return $power;
//     }
}

