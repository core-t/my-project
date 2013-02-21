<?php

class Cli_Model_Army {

    private $army;

    public function __construct($army) {
        $units = Zend_Registry::get('units');

        $this->army = $army;
        $this->army['modMovesForest'] = 3;
        $this->army['modMovesSwamp'] = 4;
        $this->army['modMovesHills'] = 5;
        $this->army['canFly'] = -count($army['heroes']) + 1;
        $this->army['canSwim'] = 0;

        foreach ($army['soldiers'] as $soldier)
        {
            $unit = $units[$soldier['unitId']];

            if ($unit['modMovesForest'] > $this->army['modMovesForest']) {
                $this->army['modMovesForest'] = $unit['modMovesForest'];
            }
            if ($unit['modMovesSwamp'] > $this->army['modMovesSwamp']) {
                $this->army['modMovesSwamp'] = $unit['modMovesSwamp'];
            }
            if ($unit['modMovesHills'] > $this->army['modMovesHills']) {
                $this->army['modMovesHills'] = $unit['modMovesHills'];
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
            $this->army['terrainCosts'] = $terrainCosts['flying'];
        } else {
            $this->army['terrainCosts'] = $terrainCosts['walking'];
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