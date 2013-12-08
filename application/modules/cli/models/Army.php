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
    }

    public function getArmy()
    {
        return $this->army;
    }

    public function calculateMovesSpend($path)
    {
        if ($this->canFly()) {
            $realPath = $this->calculateMovesSpendFlying($path);
        } elseif ($this->canSwim()) {
            $realPath = $this->calculateMovesSpendSwimming($path);
        } else {
            $realPath = $this->calculateMovesSpendWalking($path);
        }

        return array(
            'path' => $realPath,
            'currentPosition' => end($realPath)
        );
    }

    private function calculateMovesSpendFlying($path)
    {
        $terrain = Zend_Registry::get('terrain');
        $realPath = array();

        foreach ($this->army['soldiers'] as $soldier) {
            if (!$this->units[$soldier['unitId']]['canFly']) {
                continue;
            }

            if (!isset($movesLeft)) {
                $movesLeft = $soldier['movesLeft'];
                continue;
            }

            if ($movesLeft > $soldier['movesLeft']) {
                $movesLeft = $soldier['movesLeft'];
            }
        }


        for ($i = 0; $i < count($path); $i++) {
            if (!isset($path[$i]['cc'])) {
                $movesLeft -= $terrain[$path[$i]['tt']]['flying'];
            }

            if ($movesLeft < 0) {
                break;
            }

            if (isset($path[$i]['cc'])) {
                $realPath[] = array(
                    'x' => $path[$i]['x'],
                    'y' => $path[$i]['y'],
                    'tt' => $path[$i]['tt'],
                    'myCastleCosts' => true
                );
            } else {
                $realPath[] = array(
                    'x' => $path[$i]['x'],
                    'y' => $path[$i]['y'],
                    'tt' => $path[$i]['tt']
                );
            }

            if ($path[$i]['tt'] == 'E') {
                break;
            }

            if ($movesLeft == 0) {
                break;
            }
        }

        return $realPath;
    }

    private function calculateMovesSpendSwimming($path)
    {
        $terrain = Zend_Registry::get('terrain');
        $realPath = array();

        foreach ($this->army['soldiers'] as $soldier) {
            if (!$this->units[$soldier['unitId']]['canSwim']) {
                continue;
            }

            if (!isset($movesLeft)) {
                $movesLeft = $soldier['movesLeft'];
                continue;
            }

            if ($movesLeft > $soldier['movesLeft']) {
                $movesLeft = $soldier['movesLeft'];
            }
        }


        for ($i = 0; $i < count($path); $i++) {
            if (!isset($path[$i]['cc'])) {
                $movesLeft -= $terrain[$path[$i]['tt']]['swimming'];
            }

            if ($movesLeft < 0) {
                break;
            }

            if (isset($path[$i]['cc'])) {
                $realPath[] = array(
                    'x' => $path[$i]['x'],
                    'y' => $path[$i]['y'],
                    'tt' => $path[$i]['tt'],
                    'myCastleCosts' => true
                );
            } else {
                $realPath[] = array(
                    'x' => $path[$i]['x'],
                    'y' => $path[$i]['y'],
                    'tt' => $path[$i]['tt']
                );
            }

            if ($path[$i]['tt'] == 'E') {
                break;
            }

            if ($movesLeft == 0) {
                break;
            }
        }

        return $realPath;
    }

    private function calculateMovesSpendWalking($path)
    {
        $terrain = Zend_Registry::get('terrain');
        $soldiersMovesLeft = array();
        $heroesMovesLeft = array();
        $realPath = array();
        $stop = false;
        $skip = false;

        for ($i = 0; $i < count($path); $i++) {
            $defaultMoveCost = $terrain[$path[$i]['tt']]['walking'];

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
                } elseif (!isset($path[$i]['cc'])) {
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

                if (!isset($path[$i]['cc'])) {
                    $heroesMovesLeft[$hero['heroId']] -= $defaultMoveCost;
                }

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

            if (isset($path[$i]['cc'])) {
                $realPath[] = array(
                    'x' => $path[$i]['x'],
                    'y' => $path[$i]['y'],
                    'tt' => $path[$i]['tt'],
                    'myCastleCosts' => true
                );
            } else {
                $realPath[] = array(
                    'x' => $path[$i]['x'],
                    'y' => $path[$i]['y'],
                    'tt' => $path[$i]['tt']
                );
            }

            if ($path[$i]['tt'] == 'E') {
                break;
            }

            if ($stop) {
                break;
            }
        }

        return $realPath;
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
            Coret_Model_Logger::debug('addTowerDefenseModifier');
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
        $mapCastles = Zend_Registry::get('castles');

        $mCastlesInGame = new Application_Model_CastlesInGame($gameId, $db);
        $defenseModifier = $mapCastles[$castleId]['defense'] + $mCastlesInGame->getCastleDefenseModifier($castleId);

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

    public function canSwim()
    {
        if ($this->army['canSwim']) {
            return true;
        }
    }

    public function canFly()
    {
        if ($this->army['canFly'] > 0) {
            return true;
        }
    }

    public function unitsHaveRange($path)
    {
        $terrain = Zend_Registry::get('terrain');

        $soldiersMovesLeft = array();
        $heroesMovesLeft = array();

        for ($i = 0; $i < count($path); $i++) {
            $defaultMoveCost = $terrain[$path[$i]['tt']]['walking'];

            foreach ($this->army['soldiers'] as $soldier) {
                if (!isset($soldiersMovesLeft[$soldier['soldierId']])) {
                    $soldiersMovesLeft[$soldier['soldierId']] = $this->units[$soldier['unitId']]['numberOfMoves'];
                    if ($soldier['movesLeft'] <= 2) {
                        $soldiersMovesLeft[$soldier['soldierId']] += $soldier['movesLeft'];
                    } elseif ($soldier['movesLeft'] > 2) {
                        $soldiersMovesLeft[$soldier['soldierId']] += 2;
                    }
                }

                if ($this->canFly()) {
                    $soldiersMovesLeft[$soldier['soldierId']] -= $defaultMoveCost;
                } else {
                    if ($path[$i]['tt'] == 'f') {
                        $soldiersMovesLeft[$soldier['soldierId']] -= $this->units[$soldier['unitId']]['modMovesForest'];
                    } elseif ($path[$i]['tt'] == 's') {
                        $soldiersMovesLeft[$soldier['soldierId']] -= $this->units[$soldier['unitId']]['modMovesSwamp'];
                    } elseif ($path[$i]['tt'] == 'm') {
                        $soldiersMovesLeft[$soldier['soldierId']] -= $this->units[$soldier['unitId']]['modMovesHills'];
                    } else {
                        $soldiersMovesLeft[$soldier['soldierId']] -= $defaultMoveCost;
                    }
                }
            }

            foreach ($this->army['heroes'] as $hero) {
                if (!isset($heroesMovesLeft[$hero['heroId']])) {
                    $heroesMovesLeft[$hero['heroId']] = $hero['numberOfMoves'];
                    if ($hero['movesLeft'] <= 2) {
                        $heroesMovesLeft[$hero['heroId']] += $hero['movesLeft'];
                    } elseif ($hero['movesLeft'] > 2) {
                        $heroesMovesLeft[$hero['heroId']] += 2;
                    }
                }

                $heroesMovesLeft[$hero['heroId']] -= $defaultMoveCost;
            }

            if ($path[$i]['tt'] == 'E') {
                break;
            }
        }

        foreach ($soldiersMovesLeft as $s) {
            if ($s >= 0) {
                return true;
            }
        }

        foreach ($heroesMovesLeft as $h) {
            if ($h >= 0) {
                return true;
            }
        }
    }

    static public function calculateMaxArmyMoves($army)
    {
        foreach ($army['heroes'] as $hero) {
            if (!isset($heroMoves)) {
                $heroMoves = $hero['movesLeft'];
            }

            if ($hero['movesLeft'] < $heroMoves) {
                $heroMoves = $hero['movesLeft'];
            }
        }

        foreach ($army['soldiers'] as $soldier) {
            if (!isset($soldierMoves)) {
                $soldierMoves = $soldier['movesLeft'];
            }

            if ($soldier['movesLeft'] < $soldierMoves) {
                $soldierMoves = $soldier['movesLeft'];
            }
        }

        if (!isset($heroMoves)) {
            $heroMoves = 0;
        }

        if (!isset($soldierMoves)) {
            $soldierMoves = 0;
        }

        if ($heroMoves > $soldierMoves) {
            return $heroMoves;
        } else {
            return $soldierMoves;
        }
    }

    static public function heroResurrection($gameId, $heroId, $position, $playerId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $armyId = $mArmy->getArmyIdFromPosition($position);
        if (!$armyId) {
            $armyId = $mArmy->createArmy($position, $playerId);
        }
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $mHeroesInGame->addToArmy($armyId, $heroId, 0);

        return $armyId;
    }

    static public function countUnitValue($unit, $productionTime)
    {
        return ($unit['attackPoints'] + $unit['defensePoints'] + $unit['canFly']) / $productionTime;
    }

    static public function findBestCastleProduction(array $units, array $production)
    {
        $value = 0;
        $bestUnitId = null;

        foreach ($production as $unitId => $row) {

            $tmpValue = self::countUnitValue($units[$unitId], $row['time']);

            if ($tmpValue > $value) {
                $value = $tmpValue;
                $bestUnitId = $unitId;
            }
        }

        return $bestUnitId;
    }

    static public function getCastleGarrisonFromCastlePosition($castlePosition, $gameId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $ids = $mArmy->getCastleGarrisonFromCastlePosition($castlePosition);

        if ($ids) {
            $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

            return array(
                'heroes' => $mHeroesInGame->getForBattle($ids),
                'soldiers' => $mSoldier->getForBattle($ids),
                'ids' => $ids
            );
        } else {
            return array(
                'heroes' => array(),
                'soldiers' => array(),
                'ids' => array()
            );
        }

    }

    static public function getArmiesFromCastlePosition($castlePosition, $gameId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $armies = $mArmy->getArmiesFromCastlePosition($castlePosition);

        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

        foreach ($armies as $k => $army) {
            $armies[$k]['heroes'] = $mHeroesInGame->getForBattle($army['armyId']);
            $armies[$k]['soldiers'] = $mSoldier->getForBattle($army['armyId']);
        }

        return $armies;
    }

    static public function isCastleGarrisonSufficient($expectedNumberOfUnits, $armiesInCastle)
    {
        foreach ($armiesInCastle as $army) {
            if (count($army['soldiers']) == $expectedNumberOfUnits && count($army['heroes']) == 0) {
                return $army['armyId'];
            }
        }
    }

    static public function getArmyByArmyIdPlayerId($armyId, $playerId, $gameId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $result = $mArmy->getArmyByArmyIdPlayerId($armyId, $playerId);

        if (isset($result['armyId'])) {
            $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

            $result['heroes'] = $mHeroesInGame->getForMove($armyId);
            $result['soldiers'] = $mSoldier->getForMove($armyId);
            $result['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($result);
        }

        return $result;
    }

    static public function joinArmiesAtPosition($position, $playerId, $gameId, $db)
    {
        if (!isset($position['x'])) {
            throw new Exception('(joinArmiesAtPosition) No x position - exiting');

        }

        if (!isset($position['y'])) {
            throw new Exception('(joinArmiesAtPosition) No y position - exiting');
        }

        $mArmy = new Application_Model_Army($gameId, $db);
        $result = $mArmy->getArmyIdsByPositionPlayerId($position, $playerId);

        if (!isset($result[0]['armyId'])) {
//            echo '
//(joinArmiesAtPosition) Brak armii na pozycji: ';
//            Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
//            print_r($position);

            return array(
                'armyId' => null,
                'deletedIds' => null,
            );
        }

        $firstArmyId = $result[0]['armyId'];
        unset($result[0]);
        $count = count($result);

        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

        for ($i = 1; $i <= $count; $i++) {
            if ($result[$i]['armyId'] == $firstArmyId) {
                continue;
            }
            $mHeroesInGame->heroesUpdateArmyId($result[$i]['armyId'], $firstArmyId);
            $mSoldier->soldiersUpdateArmyId($result[$i]['armyId'], $firstArmyId);
            $mArmy->destroyArmy($result[$i]['armyId'], $playerId);
        }

        return array(
            'armyId' => $firstArmyId,
            'deletedIds' => $result
        );
    }

    static public function getArmyByArmyId($armyId, $gameId, $db)
    {
        $mArmy = new Application_Model_Army($gameId, $db);
        $army = $mArmy->getArmyByArmyId($armyId);

        if ($army['destroyed']) {
            $army['heroes'] = array();
            $army['soldiers'] = array();

            return $army;
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
        $army['heroes'] = $mHeroesInGame->getForMove($army['armyId']);

        $mSoldier = new Application_Model_UnitsInGame($gameId, $db);
        $army['soldiers'] = $mSoldier->getForMove($army['armyId']);

        if (empty($army['heroes']) && empty($army['soldiers'])) {
            $army['destroyed'] = true;
            $mArmy->destroyArmy($army['armyId'], $army['playerId']);
            unset($army['playerId']);

            return $army;
        } else {
            unset($army['playerId']);
            $army['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($army);
            return $army;
        }
    }
}
