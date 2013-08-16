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

    static public function getMinProductionTimeUnit(array $production)
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

    static public function isCastleAtPosition($x, $y, $castles)
    {
        $aP = array(
            'x' => $x,
            'y' => $y
        );
        foreach ($castles as $castle) {
            if (self::isCastleField($aP, $castle['position'])) {
                return $castle['castleId'];
            }
        }
        return null;
    }

    static public function isCastleField($aP, $cP)
    {
        if (($aP['x'] >= $cP['x']) && ($aP['x'] < ($cP['x'] + 2)) && ($aP['y'] >= $cP['y']) && ($aP['y'] < ($cP['y'] + 2))) {
            return true;
        }
    }

}
