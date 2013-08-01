<?php

class Application_Model_Board
{

    protected static $_instance = null;

    static public function getInstance()
    {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    static public function getCastleOptimalProduction($production)
    {
        $units = Zend_Registry::get('units');
        $arr = array();
        foreach ($production as $unitId => $unit) {
            switch ($units[$unitId]['name']) {
                case 'Griffins':
                    $arr[1] = $unitId;
                    break;
                case 'Heavy Infantry':
                    $arr[2] = $unitId;
                    break;
                case 'Archers':
                    $arr[3] = $unitId;
                    break;
                case 'Light Infantry':
                    $arr[4] = $unitId;
                    break;
                case 'Wolves':
                    $arr[5] = $unitId;
                    break;
                case 'Giants':
                    $arr[6] = $unitId;
                    break;
                case 'Cavalry':
                    $arr[7] = $unitId;
                    break;
                case 'Pegasi':
                    $arr[8] = $unitId;
                    break;
                case 'Dwarves':
                    $arr[9] = $unitId;
                    break;
            }
        }
//        asort($arr, SORT_NUMERIC);
//        throw new Exception(Zend_Debug::dump($arr));
        foreach ($arr as $unitId) {
            return $unitId;
        }
    }

    static public function getMinProductionTimeUnit($production)
    {
        $min = 100;
        foreach ($production as $key => $val) {
            if ($val['time'] < $min) {
                $min = $val['time'];
                $unitId = $key;
            }
        }
        return $unitId;
    }

//    static public function isCastle($castleId)
//    {
//        if (isset(self::$castles[$castleId])) {
//            return true;
//        }
//    }

    static public function confirmRuinPosition($position)
    {
        $ruins = Zend_Registry::get('ruins');
        foreach ($ruins as $ruinId => $ruin) {
            if ($position['x'] == $ruin['x'] && $position['y'] == $ruin['y']) {
                return $ruinId;
            }
        }
    }

    static public function isTowerAtPosition($x, $y)
    {
        $towers = Zend_Registry::get('towers');
        foreach ($towers as $tower) {
            if ($tower['x'] == $x && $tower['y'] == $y) {
                return true;
            }
        }
    }

    static public function getTerrain($type, $canFly, $canSwim)
    {
        $text = '';
        $moves = 0;
        switch ($type) {
            case 'b':
                $text = 'Bridge';
                if ($canSwim > 0) {
                    $moves = 1;
                } else if ($canFly > 0) {
                    $moves = 2;
                } else {
                    $moves = 1;
                }
                break;
            case 'B':
                $text = 'Beach';
                if ($canSwim > 0) {
                    $moves = 1;
                } else if ($canFly > 0) {
                    $moves = 2;
                } else {
                    $moves = 1;
                }
                break;
            case 'c':
                $text = 'Castle';
                $moves = 0;
                break;
            case 'e':
                $text = 'Enemy';
                $moves = null;
                break;
            case 'f':
                $text = 'Forest';
                if ($canSwim > 0) {
                    $moves = 100;
                } else if ($canFly > 0) {
                    $moves = 2;
                } else {
                    $moves = 3;
                }
                break;
            case 'g':
                $text = 'Grassland';
                if ($canSwim > 0) {
                    $moves = 100;
                } else if ($canFly > 0) {
                    $moves = 2;
                } else {
                    $moves = 2;
                }
                break;
            case 'm':
                $text = 'Hills';
                if ($canSwim > 0) {
                    $moves = 100;
                } else if ($canFly > 0) {
                    $moves = 2;
                } else {
                    $moves = 5;
                }
                break;
            case 'M':
                $text = 'Mountains';
                if ($canSwim > 0) {
                    $moves = 100;
                } else if ($canFly > 0) {
                    $moves = 2;
                } else {
                    $moves = 100;
                }
                break;
            case 'r':
                $text = 'Road';
                if ($canSwim > 0) {
                    $moves = 100;
                } else if ($canFly > 0) {
                    $moves = 2;
                } else {
                    $moves = 1;
                }
                break;
            case 's':
                $text = 'Swamp';
                if ($canSwim > 0) {
                    $moves = 100;
                } else if ($canFly > 0) {
                    $moves = 2;
                } else {
                    $moves = 4;
                }
                break;
//            case 'S':
//                $text = 'Ship';
//                $moves = 1;
//                break;
            case 'w':
                $text = 'Water';
                if ($canSwim > 0) {
                    $moves = 1;
                } else if ($canFly > 0) {
                    $moves = 2;
                } else {
                    $moves = 100;
                }
                break;
        }
        return array($text, $moves);
    }

    static public function getUnitName($unitId)
    {
        switch ($unitId) {
            case 1:
                return 'Light Infantry';
            case 2:
                return 'Heavy Infantry';
            case 3:
                return 'Cavalry';
            case 4:
                return 'Giants';
            case 5:
                return 'Wolves';
            case 6:
                return 'Navy';
            case 7:
                return 'Archers';
            case 8:
                return 'Pegasi';
            case 9:
                return 'Dwarves';
            case 10:
                return 'Griffins';
            default:
                return null;
        }
    }

    static public function isCastleField($aP, $cP)
    {
        if (($aP['x'] >= $cP['x']) && ($aP['x'] < ($cP['x'] + 2)) && ($aP['y'] >= $cP['y']) && ($aP['y'] < ($cP['y'] + 2))) {
            return true;
        }
    }

    static public function changeCasteFields($fields, $destX, $destY, $type)
    {
        $fields[$destY][$destX] = $type;
        $fields[$destY + 1][$destX] = $type;
        $fields[$destY][$destX + 1] = $type;
        $fields[$destY + 1][$destX + 1] = $type;
        return $fields;
    }

    static public function changeArmyField($fields, $destX, $destY, $type)
    {
        $fields[$destY][$destX] = $type;
        return $fields;
    }

    static public function restoreField($fields, $destX, $destY)
    {
        $mapFields = Zend_Registry::get('fields');
        $fields[$destY][$destX] = $mapFields[$destY][$destX];
        return $fields;
    }

    static public function prepareCastlesAndFields($fields, $razed, $myCastles)
    {
        $castlesSchema = Zend_Registry::get('castles');
        foreach ($castlesSchema as $castleId => $castleSchema) {
            if (isset($razed[$castleId])) {
                continue;
            }
            $x = $castleSchema['position']['x'];
            $y = $castleSchema['position']['y'];
            if (isset($myCastles[$castleId])) {
                $fields = self::changeCasteFields($fields, $x, $y, 'c');
            } else {
                $hostileCastles[$castleId] = $castleSchema;
                $hostileCastles[$castleId]['castleId'] = $castleId;
                $fields = self::changeCasteFields($fields, $x, $y, 'e');
            }
        }
        return array('hostileCastles' => $hostileCastles, 'fields' => $fields);
    }

//    static public function getHostileCastles($razed, $myCastles)
//    {
//        $castlesSchema = self::getCastlesSchema();
//        foreach ($castlesSchema as $castleId => $castleSchema) {
//            if (isset($razed[$castleId]) || isset($myCastles[$castleId])) {
//                continue;
//            }
//            $hostileCastles[$castleId] = $castleSchema;
//            $hostileCastles[$castleId]['castleId'] = $castleId;
//        }
//        return $hostileCastles;
//    }

    static public function isCastleAtPosition($x, $y, $castles)
    {
        $aP = array(
            'x' => $x,
            'y' => $y
        );
        foreach ($castles as $castle) {
            if (self::isCastleFild($aP, $castle['position'])) {
                return $castle['castleId'];
            }
        }
        return null;
    }

    static public function isCastleFild($aP, $cP)
    {
        if (($aP['x'] >= $cP['x']) && ($aP['x'] < ($cP['x'] + 2)) && ($aP['y'] >= $cP['y']) && ($aP['y'] < ($cP['y'] + 2))) {
            return true;
        }
    }
}
