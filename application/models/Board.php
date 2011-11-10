<?php

class Application_Model_Board {

    protected static $castles;
    protected static $fields;
    protected static $_instance = null;

    private function __construct() {
//         new Game_Logger('Singleton dupa!!!');
        self::$castles = array(
            0 => array(
                'name' => 'MARTHOS',
                'income' => 20,
                'defensePoints' => 4,
                'position' => array('x' => 62, 'y' => 56),
                'capital' => true,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
                    'Cavalry' => array('time' => '4', 'cost' => '8'),
                    'Pegasi' => array('time' => '7', 'cost' => '16'),
                )
            ),
            1 => array(
                'name' => 'ELVALLIE',
                'income' => 33,
                'defensePoints' => 4,
                'position' => array('x' => 41, 'y' => 37),
                'capital' => true,
                'production' => array(
                    'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
                    'Archers' => array('time' => '1', 'cost' => '4'),
                    'Pegasi' => array('time' => '6', 'cost' => '16'),
                )
            ),
            2 => array(
                'name' => 'CHARLING',
                'income' => 16,
                'defensePoints' => 1,
                'position' => array('x' => 48, 'y' => 34),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                )
            ),
            3 => array(
                'name' => 'GILDENHOME',
                'income' => 24,
                'defensePoints' => 3,
                'position' => array('x' => 33, 'y' => 38),
                'capital' => false,
                'production' => array(
                    'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
                    'Archers' => array('time' => '1', 'cost' => '4'),
                    'Pegasi' => array('time' => '7', 'cost' => '16'),
                )
            ),
            4 => array(
                'name' => 'LOREMARK',
                'income' => 3,
                'defensePoints' => 2,
                'position' => array('x' => 43, 'y' => 45),
                'capital' => false,
                'production' => array(
                    'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
                    'Archers' => array('time' => '1', 'cost' => '4'),
                    'Pegasi' => array('time' => '7', 'cost' => '16'),
                )
            ),
            5 => array(
                'name' => 'ARGENTHORN',
                'income' => 22,
                'defensePoints' => 2,
                'position' => array('x' => 42, 'y' => 26),
                'capital' => false,
                'production' => array(
                    'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
                    'Archers' => array('time' => '1', 'cost' => '4'),
                    'Pegasi' => array('time' => '7', 'cost' => '16'),
                )
            ),
            6 => array(
                'name' => 'ANGBAR',
                'income' => 20,
                'defensePoints' => 2,
                'position' => array('x' => 54, 'y' => 22),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Cavalry' => array('time' => '4', 'cost' => '8'),
                )
            ),
            7 => array(
                'name' => 'SSURI',
                'income' => 19,
                'defensePoints' => 2,
                'position' => array('x' => 59, 'y' => 24),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Cavalry' => array('time' => '4', 'cost' => '8'),
                )
            ),
            8 => array(
                'name' => 'TROY',
                'income' => 13,
                'defensePoints' => 1,
                'position' => array('x' => 56, 'y' => 27),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Cavalry' => array('time' => '4', 'cost' => '8'),
                )
            ),
            9 => array(
                'name' => 'HEREUTH',
                'income' => 26,
                'defensePoints' => 4,
                'position' => array('x' => 65, 'y' => 35),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
                )
            ),
            10 => array(
                'name' => 'GLUK',
                'income' => 17,
                'defensePoints' => 2,
                'position' => array('x' => 84, 'y' => 42),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Giants' => array('time' => '3', 'cost' => '4'),
                    'Wolves' => array('time' => '3', 'cost' => '8'),
                )
            ),
            11 => array(
                'name' => 'GORK',
                'income' => 15,
                'defensePoints' => 2,
                'position' => array('x' => 86, 'y' => 45),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Giants' => array('time' => '5', 'cost' => '4'),
                    'Wolves' => array('time' => '3', 'cost' => '8'),
                )
            ),
            12 => array(
                'name' => 'GAROM',
                'income' => 20,
                'defensePoints' => 2,
                'position' => array('x' => 87, 'y' => 27),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Wolves' => array('time' => '3', 'cost' => '8'),
                )
            ),
            13 => array(
                'name' => 'BALAD NARAN',
                'income' => 29,
                'defensePoints' => 4,
                'position' => array('x' => 90, 'y' => 16),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
                    'Wolves' => array('time' => '2', 'cost' => '8'),
                    'Navy' => array('time' => '11', 'cost' => '20'),
                )
            ),
            14 => array(
                'name' => 'GALIN',
                'income' => 20,
                'defensePoints' => 2,
                'position' => array('x' => 41, 'y' => 0),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Cavalry' => array('time' => '8', 'cost' => '10'),
                    'Navy' => array('time' => '11', 'cost' => '20'),
                )
            ),
            15 => array(
                'name' => 'KOR',
                'income' => 30,
                'defensePoints' => 4,
                'position' => array('x' => 100, 'y' => 3),
                'capital' => true,
                'production' => array(
                    'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
                    'Giants' => array('time' => '5', 'cost' => '6'),
                    'Wolves' => array('time' => '3', 'cost' => '8'),
                )
            ),
            16 => array(
                'name' => 'DETHAL',
                'income' => 20,
                'defensePoints' => 2,
                'position' => array('x' => 89, 'y' => 0),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Wolves' => array('time' => '5', 'cost' => '8'),
                )
            ),
            17 => array(
                'name' => 'THURTZ',
                'income' => 18,
                'defensePoints' => 2,
                'position' => array('x' => 85, 'y' => 2),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Wolves' => array('time' => '3', 'cost' => '8'),
                )
            ),
            18 => array(
                'name' => 'DARCLAN',
                'income' => 23,
                'defensePoints' => 2,
                'position' => array('x' => 78, 'y' => 0),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Wolves' => array('time' => '3', 'cost' => '8'),
                )
            ),
            19 => array(
                'name' => 'ILNYR',
                'income' => 21,
                'defensePoints' => 2,
                'position' => array('x' => 71, 'y' => 6),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
                    'Navy' => array('time' => '11', 'cost' => '20'),
                )
            ),
            20 => array(
                'name' => 'DUINOTH',
                'income' => 19,
                'defensePoints' => 2,
                'position' => array('x' => 68, 'y' => 18),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Cavalry' => array('time' => '5', 'cost' => '8'),
                )
            ),
            21 => array(
                'name' => 'KAZRACK',
                'income' => 21,
                'defensePoints' => 2,
                'position' => array('x' => 58, 'y' => 3),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
                    'Navy' => array('time' => '11', 'cost' => '20'),
                )
            ),
            22 => array(
                'name' => 'VERNON',
                'income' => 24,
                'defensePoints' => 3,
                'position' => array('x' => 48, 'y' => 2),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Heavy Infantry' => array('time' => '3', 'cost' => '4'),
                    'Navy' => array('time' => '11', 'cost' => '20'),
                )
            ),
            23 => array(
                'name' => 'HIMELTON',
                'income' => 14,
                'defensePoints' => 1,
                'position' => array('x' => 22, 'y' => 8),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Cavalry' => array('time' => '6', 'cost' => '8'),
                )
            ),
            24 => array(
                'name' => 'STORMHEIM',
                'income' => 20,
                'defensePoints' => 4,
                'position' => array('x' => 19, 'y' => 20),
                'capital' => true,
                'production' => array(
                    'Giants' => array('time' => '2', 'cost' => '4'),
                )
            ),
            25 => array(
                'name' => 'OHMSMOUTH',
                'income' => 24,
                'defensePoints' => 3,
                'position' => array('x' => 7, 'y' => 7),
                'capital' => false,
                'production' => array(
                    'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
                    'Navy' => array('time' => '10', 'cost' => '18'),
                )
            ),
            26 => array(
                'name' => 'WELLMORE',
                'income' => 20,
                'defensePoints' => 2,
                'position' => array('x' => 4, 'y' => 16),
                'capital' => false,
                'production' => array(
                    'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
                    'Navy' => array('time' => '11', 'cost' => '20'),
                )
            ),
            27 => array(
                'name' => 'TASME',
                'income' => 19,
                'defensePoints' => 2,
                'position' => array('x' => 8, 'y' => 25),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Navy' => array('time' => '11', 'cost' => '20'),
                )
            ),
            28 => array(
                'name' => 'VARDE',
                'income' => 23,
                'defensePoints' => 2,
                'position' => array('x' => 8, 'y' => 34),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Cavalry' => array('time' => '5', 'cost' => '8'),
                    'Navy' => array('time' => '11', 'cost' => '20'),
                )
            ),
            29 => array(
                'name' => 'QUIESCE',
                'income' => 3,
                'defensePoints' => 2,
                'position' => array('x' => 15, 'y' => 32),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Navy' => array('time' => '11', 'cost' => '20'),
                )
            ),
            30 => array(
                'name' => 'KHORFE',
                'income' => 26,
                'defensePoints' => 3,
                'position' => array('x' => 8, 'y' => 42),
                'capital' => false,
                'production' => array(
                    'Dwarves' => array('time' => '2', 'cost' => '4'),
                    'Griffins' => array('time' => '2', 'cost' => '4'),
                )
            ),
            31 => array(
                'name' => 'ALFAR\'S GAP',
                'income' => 18,
                'defensePoints' => 2,
                'position' => array('x' => 22, 'y' => 54),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                    'Cavalry' => array('time' => '5', 'cost' => '8'),
                )
            ),
            32 => array(
                'name' => 'LADOR',
                'income' => 16,
                'defensePoints' => 2,
                'position' => array('x' => 31, 'y' => 18),
                'capital' => false,
                'production' => array(
                    'Light Infantry' => array('time' => '1', 'cost' => '4'),
                )
            )
        );
    }

    static public function getInstance() {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    static public function getDefaultStartPositions() {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }
        return array(
            'white' => array(
                'id' => 0,
                'position' => self::$castles[0]['position']
            ),
            'green' => array(
                'id' => 1,
                'position' => self::$castles[1]['position']
            ),
            'red' => array(
                'id' => 15,
                'position' => self::$castles[15]['position']
            ),
            'yellow' => array(
                'id' => 24,
                'position' => self::$castles[24]['position']
            )
        );
    }

    static public function getCastlesSchema() {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }
        return self::$castles;
    }

    static public function getCastle($castleId) {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }
        return self::$castles[$castleId];
    }

    static public function getCastlePosition($castleId) {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }
        return self::$castles[$castleId]['position'];
    }

    static public function getCastleDefense($castleId) {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }
        return self::$castles[$castleId]['defensePoints'];
    }

    static public function getCastleOptimalProduction($castleId) {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }
        $units = array();
        foreach (self::$castles[$castleId]['production'] as $unitName => $productionUnit) {
            switch ($unitName) {
                case 'Griffins':
                    $units[1] = $unitName;
                    break;
                case 'Pegasi':
                    $units[2] = $unitName;
                    break;
                case 'Wolves':
                    $units[3] = $unitName;
                    break;
                case 'Giants':
                    $units[4] = $unitName;
                    break;
                case 'Cavalry':
                    $units[5] = $unitName;
                    break;
                case 'Dwarves':
                    $units[6] = $unitName;
                    break;
                case 'Heavy Infantry':
                    $units[7] = $unitName;
                    break;
                case 'Archers':
                    $units[8] = $unitName;
                    break;
                case 'Light Infantry':
                    $units[9] = $unitName;
                    break;
            }
        }
        asort($units, SORT_NUMERIC);
//        throw new Exception(Zend_Debug::dump($units));
        foreach ($units as $unit) {
            return $unit;
        }
    }

    static public function getMinProductionTimeUnit($castleId) {
        $castle = self::getCastle($castleId);
        $min = 100;
        foreach ($castle['production'] as $key => $val) {
            if ($val['time'] < $min) {
                $min = $val['time'];
                $unitName = $key;
            }
        }
        return $unitName;
    }

    static public function isCastle($castleId) {
        if (isset(self::$castles[$castleId])) {
            return true;
        }
    }

    static public function getRuins() {
        $ruins = array();
        $ruins[0] = array('x' => 34, 'y' => 58);
        $ruins[1] = array('x' => 37, 'y' => 58);
        $ruins[2] = array('x' => 18, 'y' => 46);
        $ruins[3] = array('x' => 24, 'y' => 39);
        $ruins[4] = array('x' => 24, 'y' => 35);
        $ruins[5] = array('x' => 11, 'y' => 30);
        $ruins[6] = array('x' => 16, 'y' => 12);
        $ruins[7] = array('x' => 38, 'y' => 6);
        $ruins[8] = array('x' => 42, 'y' => 31);
        $ruins[9] = array('x' => 66, 'y' => 15);
        $ruins[10] = array('x' => 69, 'y' => 45);
        $ruins[11] = array('x' => 74, 'y' => 60);
        $ruins[12] = array('x' => 77, 'y' => 34);
        $ruins[13] = array('x' => 82, 'y' => 30);
        $ruins[14] = array('x' => 87, 'y' => 38);
        $ruins[15] = array('x' => 94, 'y' => 35);
        $ruins[16] = array('x' => 105, 'y' => 46);
        $ruins[17] = array('x' => 100, 'y' => 22);
        return $ruins;
    }

    static public function confirmRuinPosition($position) {
        $ruins = self::getRuins();
        foreach ($ruins as $ruinId => $ruin) {
            if ($position['x'] == $ruin['x'] && $position['y'] == $ruin['y']) {
                return $ruinId;
            }
        }
    }

    static public function getTowers() {
        $towers = array();
        $towers[] = array('x' => 95, 'y' => 3);
        $towers[] = array('x' => 91, 'y' => 5);
        $towers[] = array('x' => 81, 'y' => 9);
        $towers[] = array('x' => 72, 'y' => 12);
        $towers[] = array('x' => 91, 'y' => 12);
        $towers[] = array('x' => 93, 'y' => 12);
        $towers[] = array('x' => 27, 'y' => 14);
        $towers[] = array('x' => 52, 'y' => 14);
        $towers[] = array('x' => 54, 'y' => 14);
        $towers[] = array('x' => 91, 'y' => 23);
        $towers[] = array('x' => 63, 'y' => 24);
        $towers[] = array('x' => 90, 'y' => 25);
        $towers[] = array('x' => 72, 'y' => 31);
        $towers[] = array('x' => 73, 'y' => 34);
        $towers[] = array('x' => 55, 'y' => 35);
        $towers[] = array('x' => 73, 'y' => 36);
        $towers[] = array('x' => 14, 'y' => 37);
        $towers[] = array('x' => 22, 'y' => 38);
        $towers[] = array('x' => 57, 'y' => 39);
        $towers[] = array('x' => 73, 'y' => 39);
        $towers[] = array('x' => 15, 'y' => 40);
        $towers[] = array('x' => 22, 'y' => 40);
        $towers[] = array('x' => 72, 'y' => 42);
        $towers[] = array('x' => 57, 'y' => 46);
        $towers[] = array('x' => 50, 'y' => 49);
        $towers[] = array('x' => 70, 'y' => 50);
        $towers[] = array('x' => 20, 'y' => 51);
        $towers[] = array('x' => 50, 'y' => 51);
        $towers[] = array('x' => 56, 'y' => 51);
        $towers[] = array('x' => 60, 'y' => 54);
        $towers[] = array('x' => 67, 'y' => 54);
        return $towers;
    }

    static public function isTowerAtPosition($x, $y) {
        $towers = self::getTowers();
        foreach ($towers as $k => $tower) {
            if ($tower['x'] == $x && $tower['y'] == $y) {
                return true;
            }
        }
    }

    static public function getTerrain($type, $canFly, $canSwim) {
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
            case 'S':
                $text = 'Ship';
                $moves = 1;
                break;
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

    static public function getUnitId($name) {
        switch ($name) {
            case 'Light Infantry':
                return 1;
            case 'Heavy Infantry':
                return 2;
            case 'Cavalry':
                return 3;
            case 'Giants':
                return 4;
            case 'Wolves':
                return 5;
            case 'Navy':
                return 6;
            case 'Archers':
                return 7;
            case 'Pegasi':
                return 8;
            case 'Dwarves':
                return 9;
            case 'Griffins':
                return 10;
            default:
                return null;
        }
    }

    static public function getUnitName($unitId) {
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

    static public function isCastleFild($aP, $cP) {
        if (($aP['x'] >= $cP['x']) && ($aP['x'] < ($cP['x'] + 2)) && ($aP['y'] >= $cP['y']) && ($aP['y'] < ($cP['y'] + 2))) {
            return true;
        }
    }

    static public function changeCasteFields($fields, $destX, $destY, $type) {
        $fields[$destY][$destX] = $type;
        $fields[$destY + 1][$destX] = $type;
        $fields[$destY][$destX + 1] = $type;
        $fields[$destY + 1][$destX + 1] = $type;
        return $fields;
    }

    static public function changeArmyField($fields, $destX, $destY, $type) {
        $fields[$destY][$destX] = $type;
        return $fields;
    }

    static public function restoreField($fields, $destX, $destY) {
        $fields[$destY][$destX] = self::$fields[$destY][$destX];
        return $fields;
    }

    static public function prepareCastlesAndFields($fields, $razed, $myCastles) {
        $castlesSchema = self::getCastlesSchema();
        foreach ($castlesSchema as $castleId => $castleSchema) {
            if (isset($razed[$castleId])) {
                continue;
            }
            $x = $castleSchema['position']['x'] / 40;
            $y = $castleSchema['position']['y'] / 40;
            if (isset($myCastles[$castleId])) {
                $fields = self::changeCasteFields($fields, $x, $y, 'c');
            } else {
                $hostileCastles[$castleId] = $castleSchema;
                $fields = self::changeCasteFields($fields, $x, $y, 'e');
            }
        }
        return array('hostileCastles' => $hostileCastles, 'fields' => $fields);
    }

    static public function isArmyInCastle($x, $y, $castles) {
        $aP = array(
            'x' => $x,
            'y' => $y
        );
        foreach ($castles as $castle) {
            if (self::isCastleFild($aP, self::getCastlePosition($castle['castleId']))) {
                return $castle['castleId'];
            }
        }
        return null;
    }

    static public function getBoardFields() {
        // x*y = 108*68 = 7344
        self::$fields = array(
        0 => array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'm', 'r', 'r', 'm', 'm', 'M', 'M', 'm', 'm', 'm', 'm', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'r', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'm', 'g', 'm', 'M', 'M', 'm', 'w', 'w', 'm', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g'
        ),
        1 => array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'm', 'g', 'g', 'g', 'w', 'w', 'm', 'm', 'g', 'g', 'g', 'g', 'f', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'g'
        ),
        2 => array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'w', 'w', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'g'
        ),
        3 => array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'm', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'g', 'g', 'w', 'w', 'g', 'g', 'g', 'w', 'w', 'w', 'g', 'g', 'g', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'M', 'm', 'g'
        ),
        4 => array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'g', 'm', 'm', 'g', 'g', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'g', 'w', 'w', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'w', 'w', 'w', 'g', 'g', 'g', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'm', 'g', 'g', 'g', 'g', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'M', 'm', 'g'
        ),
        5 => array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'r', 'r', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'g', 'g', 'g', 'g', 'w', 'g', 'f', 'w', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'g', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'g'
        ),
        6 => array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'f', 'g', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'r', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g'
        ),
        7 => array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'm', 'm', 'g', 'w', 'w', 'f', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'r', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g'
        ),
        8 => array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'm', 'g', 'w', 'g', 'g', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g'
        ),
        9 => array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'r', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g'
        ),
        10 => array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'g', 'g', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'r', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'm', 'g', 'g'
        ),
        11 => array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'f', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'f', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'f', 'f', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'm', 'g', 'g'
        ),
        12 => array(
            'w', 'w', 'w', 'w', 'w', 'f', 'w', 'w', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'f', 'f', 'g', 'g', 'g', 'r', 'g', 'f', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'm', 'M', 'M', 'm', 'g', 'g'
        ),
        13 => array(
            'w', 'w', 'w', 'w', 'w', 'f', 'w', 'w', 'w', 'f', 'g', 'g', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'f', 'f', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'w', 'w', 'r', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'm', 'M', 'M', 'm', 'g', 'g'
        ),
        14 => array(
            'w', 'w', 'w', 'w', 'w', 'f', 'w', 'w', 'w', 'g', 'g', 'r', 'r', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'g', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'g', 'm', 'm', 'm', 'm', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'w', 'w', 'w', 'r', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'm', 'g', 'g'
        ),
        15 => array(
            'w', 'w', 'w', 'w', 'g', 'g', 'r', 'r', 'r', 'r', 'r', 'r', 'f', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'f', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'm', 'g', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'm', 'm', 'm', 'm', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'r', 'g', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'g'
        ),
        16 => array(
            'w', 'w', 'w', 'w', 'g', 'g', 'r', 'w', 'w', 'w', 'g', 'r', 'f', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'm', 'm', 'm', 'm', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'f', 'g', 'g', 'r', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'g'
        ),
        17 => array(
            'w', 'w', 'w', 'w', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'r', 'f', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'm', 'm', 'm', 'g', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'g'
        ),
        18 => array(
            'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'w', 'w', 'w', 'r', 'f', 'f', 'f', 'f', 'f', 'r', 'r', 'r', 'm', 'm', 'm', 'f', 'f', 'g', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'f', 'f', 'f', 'w', 'w', 'w', 'w', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'g'
        ),
        19 => array(
            'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'f', 'w', 'w', 'r', 'r', 'f', 'f', 'f', 'f', 'm', 'm', 'r', 'm', 'm', 'm', 'f', 'f', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'r', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'w', 'g', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'm', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'g', 'g'
        ),
        20 => array(
            'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'r', 'f', 'f', 'f', 'f', 'm', 'm', 'g', 'g', 'm', 'm', 'f', 'f', 'm', 'm', 'g', 'g', 'g', 'g', 'f', 'f', 'r', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'w', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'g', 'g'
        ),
        21 => array(
            'w', 'w', 'w', 'w', 'g', 'f', 'f', 'g', 'g', 'w', 'w', 'w', 'r', 'f', 'f', 'f', 'f', 'm', 'm', 'g', 'g', 'm', 'm', 'f', 'f', 'm', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'r', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'g', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'f', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g'
        ),
        22 => array(
            'w', 'w', 'w', 'w', 'f', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'g', 'f', 'f', 'f', 'm', 'm', 'm', 'm', 'm', 'm', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'f', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'm', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'g', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g'
        ),
        23 => array(
            'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'g', 'f', 'f', 'f', 'm', 'g', 'm', 'm', 'g', 'g', 'f', 'f', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'r', 'r', 'f', 'f', 'f', 'w', 'w', 'g', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'w', 'g', 'g', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g'
        ),
        24 => array(
            'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'g', 'g', 'f', 'f', 'f', 'f', 'm', 'g', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'w', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g'
        ),
        25 => array(
            'w', 'w', 'w', 'w', 'f', 'f', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'r', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'f', 'g', 'g', 'g', 'g', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'g', 'w', 'w', 'w', 'w', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'g'
        ),
        26 => array(
            'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'r', 'r', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'g'
        ),
        27 => array(
            'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'r', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g'
        ),
        28 => array(
            'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'w', 'r', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'g', 'g'
        ),
        29 => array(
            'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'w', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'g', 'g'
        ),
        30 => array(
            'w', 'w', 'w', 'f', 'f', 'f', 'g', 'g', 'w', 'w', 'w', 'g', 'w', 'w', 'w', 'r', 'r', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'w', 'w', 'g', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g'
        ),
        31 => array(
            'w', 'w', 'w', 'f', 'f', 'f', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'r', 'r', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'g', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'w', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g'
        ),
        32 => array(
            'w', 'w', 'w', 'f', 'f', 'f', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'r', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'g'
        ),
        33 => array(
            'w', 'w', 'w', 'f', 'f', 'f', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'r', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'm', 'g', 'm', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'g'
        ),
        34 => array(
            'w', 'w', 'w', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'g', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'g'
        ),
        35 => array(
            'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'm', 'm', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'g', 'g', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'f', 'f', 'f', 'f', 'g', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g'
        ),
        36 => array(
            'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'f', 'g', 'g', 'g', 'g', 'g', 'r', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g'
        ),
        37 => array(
            'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'f', 'f', 'f', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g'
        ),
        38 => array(
            'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'r', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'f', 'f', 'w', 'w', 'f', 'f', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'r', 'g', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g'
        ),
        39 => array(
            'w', 'w', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'r', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'g', 'r', 'g', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'g', 'g'
        ),
        40 => array(
            'w', 'g', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'r', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'r', 'r', 'r', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'g', 'r', 'g', 'f', 'f', 'f', 'f', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g'
        ),
        41 => array(
            'm', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'g', 'w', 'w', 'w', 'r', 'g', 'g', 'g', 'm', 'g', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'm', 'm', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'f', 'f', 'g', 'g'
        ),
        42 => array(
            'm', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'w', 'w', 'w', 'w', 'r', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'm', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'f', 'f', 'f', 'g', 'g'
        ),
        43 => array(
            'm', 'm', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'w', 'w', 'w', 'w', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'm', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'f', 'f', 'f', 'f', 'g', 'g'
        ),
        44 => array(
            'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'm', 'M', 'M', 'M', 'M', 'm', 'g', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'f', 'f', 'f', 'f', 'f', 'g', 'g'
        ),
        45 => array(
            'g', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'g', 'f', 'f', 'f', 'f', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g'
        ),
        46 => array(
            'g', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'm', 'm', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g'
        ),
        47 => array(
            'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'r', 'm', 'm', 'm', 'm', 'g', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        48 => array(
            'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        49 => array(
            'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        50 => array(
            'g', 'g', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        51 => array(
            'g', 'g', 'g', 'm', 'm', 'M', 'f', 'f', 'f', 'f', 'f', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'm', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        52 => array(
            'g', 'g', 'g', 'g', 'm', 'g', 'm', 'm', 'm', 'M', 'f', 'f', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'r', 'r', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'm', 'w', 'w', 'w', 'w', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        53 => array(
            'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'f', 'f', 'f', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'r', 'r', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'm', 'w', 'w', 'w', 'w', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        54 => array(
            'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'f', 'f', 'f', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'm', 'm', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'm', 'w', 'w', 'w', 'w', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        55 => array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'm', 'm', 'M', 'f', 'f', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'm', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'm', 'w', 'w', 'w', 'w', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        56 => array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'f', 'f', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'r', 'w', 'w', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'm', 'M', 'M', 'm', 'w', 'w', 'w', 'w', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        57 => array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'g', 'r', 'w', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'g', 'M', 'M', 'm', 'w', 'w', 'w', 'w', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        58 => array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'f', 'f', 'm', 'm', 'g', 'w', 'w', 'g', 'f', 'f', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'f', 'g', 'g', 'g', 'M', 'M', 'm', 'w', 'w', 'w', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        59 => array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'w', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'M', 'M', 'm', 'g', 'w', 'g', 'g', 'm', 'M', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        60 => array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        61 => array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'm', 'm', 'M', 'M', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'M', 'M', 'M', 'M', 'g', 'g', 'M', 'M', 'M', 'M', 'M', 'M', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        62 => array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'g', 'g', 'g', 'M', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        63 => array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        64 => array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        65 => array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'M', 'M', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        66 => array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'g', 'g', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'g', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        67 => array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ),
        68 => array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        ));

        return self::$fields;
    }

}
