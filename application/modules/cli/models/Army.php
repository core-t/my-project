<?php

class Cli_Model_Army
{

    private $army;
    private $units;

    public function __construct($army)
    {
        $this->units = Zend_Registry::get('units');

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
            $unit = $this->units[$soldier['unitId']];

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

    public function getArmy()
    {
        return $this->army;
    }

    static public function getTerrainCosts()
    {
        return array(
            'flying' => array(
                'b' => 2,
                'c' => 0,
                'e' => null,
                'E' => 2,
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
                'E' => 2,
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
                'E' => 2,
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
                'E' => 2,
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

    public function calculateMovesSpend($path)
    {
        $soldiersMovesLeft = array();
        $heroesMovesLeft = array();
        $realPath = array();
        $stop = false;
        $skip = false;

        for ($i = 0; $i < count($path); $i++) {
            $defaultMoveCost = $this->army['terrainCosts'][$path[$i]['tt']];

            foreach ($this->army['soldiers'] as $soldier) {
                if (!isset($soldiersMovesLeft[$soldier['soldierId']])) {
                    $soldiersMovesLeft[$soldier['soldierId']] = $soldier['movesLeft'];
                }

                if ($path[$i]['tt'] == 'f') {
                    $soldiersMovesLeft[$soldier['soldierId']] -= $this->units[$soldier['unitId']]['modMovesForest'];
                } elseif ($path[$i]['tt'] == 's') {
                    $soldiersMovesLeft[$soldier['soldierId']] -= $this->units[$soldier['unitId']]['modMovesSwamp'];
                } elseif ($path[$i]['tt'] == 'm') {
                    $soldiersMovesLeft[$soldier['soldierId']] -= $this->units[$soldier['unitId']]['modMovesHills'];
                } else {
                    $soldiersMovesLeft[$soldier['soldierId']] -= $defaultMoveCost;
                }

                if ($soldiersMovesLeft[$soldier['soldierId']] < 0) {
                    $skip = true;
                }

                if ($soldiersMovesLeft[$soldier['soldierId']] <= 0) {
                    $stop = true;
                    break;
                }
            }

            foreach ($this->army['heroes'] as $hero) {
                if (!isset($heroesMovesLeft[$hero['heroId']])) {
                    $heroesMovesLeft[$hero['heroId']] = $hero['movesLeft'];
                }

                $heroesMovesLeft[$hero['heroId']] -= $defaultMoveCost;

                if ($heroesMovesLeft[$hero['heroId']] < 0) {
                    $skip = true;
                }

                if ($heroesMovesLeft[$hero['heroId']] <= 0) {
                    $stop = true;
                    break;
                }
            }

            if ($skip) {
                break;
            }

            $realPath[] = array(
                'x' => $path[$i]['x'],
                'y' => $path[$i]['y']
            );

            if ($stop) {
                break;
            }

        }

        return array(
            'path' => $realPath,
            'currentPosition' => end($realPath)
        );
    }

    static public function setCombatDefenseModifiers($army)
    {
        if ($army['heroes']) {
            if (isset($army['defenseModifier'])) {
                $army['defenseModifier']++;
            } else {
                $army['defenseModifier'] = 1;
            }
        }
        return $army;
    }

    static public function addTowerDefenseModifier($army)
    {
        if (!isset($army['x'])) {
            Cli_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
            exit;
        }
        if (Application_Model_Board::isTowerAtPosition($army['x'], $army['y'])) {
            if (isset($army['defenseModifier'])) {
                $army['defenseModifier']++;
            } else {
                $army['defenseModifier'] = 1;
            }
        }
        return $army;
    }

    static public function addCastleDefenseModifier($army, $gameId, $castleId, $db)
    {
        $defenseModifier = Application_Model_Board::getCastleDefense($castleId) + Cli_Model_Database::getCastleDefenseModifier($gameId, $castleId, $db);
        if ($defenseModifier > 0) {
            if (isset($army['defenseModifier'])) {
                $army['defenseModifier'] += $defenseModifier;
            } else {
                $army['defenseModifier'] = $defenseModifier;
            }
        } else {
            echo 'error! !';
        }
        return $army;
    }

    static public function armyArray($columnName = '')
    {
        return array('armyId', 'destroyed', 'fortified', 'x', 'y', $columnName);
    }


}