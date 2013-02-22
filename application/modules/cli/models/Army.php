<?php

class Cli_Model_Army {

    private $army;

    public function __construct($army) {
        $units = Zend_Registry::get('units');

        $this->army = $army;
        $this->army['defenseModifier'] = 0;
        $this->army['attackModifier'] = 0;

        $numberOfHeroes = count($army['heroes']);
        if ($numberOfHeroes) {
            $this->army['defenseModifier']++;
            $this->army['attackModifier']++;
            $modMovesForest = 3;
            $modMovesSwamp = 4;
            $modMovesHills = 5;
        } else {
            $modMovesForest = 0;
            $modMovesSwamp = 0;
            $modMovesHills = 0;
        }
        $this->army['canFly'] = -$numberOfHeroes + 1;
        $this->army['canSwim'] = 0;

        foreach ($army['soldiers'] as $soldier) {
            $unit = $units[$soldier['unitId']];

            if ($unit['modMovesForest'] > $modMovesForest) {
                $modMovesForest = $unit['modMovesForest'];
            }
            if ($unit['modMovesSwamp'] > $modMovesSwamp) {
                $modMovesSwamp = $unit['modMovesSwamp'];
            }
            if ($unit['modMovesHills'] > $modMovesHills) {
                $modMovesHills = $unit['modMovesHills'];
            }

            if ($unit['canFly']) {
                $this->army['canFly']++;
            } else {
                $this->army['canFly'] -= 200;
            }
            if ($unit['canSwim']) {
                $this->army['canSwim']++;
            }
        }

        $terrainCosts = self::getTerrainCosts();

        if ($this->army['canSwim']) {
            $this->army['terrainCosts'] = $terrainCosts['swimming'];
        } elseif ($this->army['canFly'] > 0) {
            $this->army['attackModifier']++;
            $this->army['terrainCosts'] = $terrainCosts['flying'];
        } else {
            $this->army['terrainCosts'] = $terrainCosts['walking'];
            $this->army['terrainCosts']['f'] = $modMovesForest;
            $this->army['terrainCosts']['s'] = $modMovesSwamp;
            $this->army['terrainCosts']['m'] = $modMovesHills;
        }
    }

    public function getArmy() {
        return $this->army;
    }

    static public function getTerrainCosts() {
        return array(
            'flying' => array(
                'b' => 2,
                'c' => 0,
                'e' => null,
                'f' => 2,
                'g' => 2,
                'm' => 2,
                'M' => 2,
                'r' => 1,
                's' => 2,
                'S' => 2,
                'w' => 2
            ),
            'swimming' => array(
                'b' => 0,
                'c' => 0,
                'e' => null,
                'f' => 300,
                'g' => 200,
                'm' => 500,
                'M' => 1000,
                'r' => 100,
                's' => 400,
                'S' => 0,
                'w' => 0
            ),
            'ship' => array(
                'b' => 1,
                'c' => 0,
                'e' => null,
                'f' => 300,
                'g' => 200,
                'm' => 500,
                'M' => 1000,
                'r' => 100,
                's' => 400,
                'S' => 1,
                'w' => 1
            ),
            'walking' => array(
                'b' => 1,
                'c' => 0,
                'e' => null,
                'f' => 3,
                'g' => 2,
                'm' => 5,
                'M' => 1000,
                'r' => 1,
                's' => 4,
                'S' => 1,
                'w' => 50
            )
        );
    }

}