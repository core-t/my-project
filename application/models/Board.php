<?php

class Application_Model_Board {

    private $_castles = array();

    public function __construct() {
 $this->_castles[0] = array(
'name' => 'MARTHOS',
'income' => 20,
'defensePoints' => 6,
'position' => array('x' => 2480, 'y' => 2240),
'capital' => true,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
'Cavalry' => array('time' => '4', 'cost' => '8'),
'Pegasi' => array('time' => '7', 'cost' => '16'),
)
);
$this->_castles[1] = array(
'name' => 'ELVALLIE',
'income' => 33,
'defensePoints' => 6,
'position' => array('x' => 1640, 'y' => 1480),
'capital' => true,
'production' => array(
'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
'Archers' => array('time' => '1', 'cost' => '4'),
'Pegasi' => array('time' => '6', 'cost' => '16'),
)
);
$this->_castles[2] = array(
'name' => 'CHARLING',
'income' => 16,
'defensePoints' => 3,
'position' => array('x' => 1920, 'y' => 1360),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
)
);
$this->_castles[3] = array(
'name' => 'GILDENHOME',
'income' => 24,
'defensePoints' => 5,
'position' => array('x' => 1320, 'y' => 1520),
'capital' => false,
'production' => array(
'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
'Archers' => array('time' => '1', 'cost' => '4'),
'Pegasi' => array('time' => '7', 'cost' => '16'),
)
);
$this->_castles[4] = array(
'name' => 'LOREMARK',
'income' => 3,
'defensePoints' => 4,
'position' => array('x' => 1720, 'y' => 1800),
'capital' => false,
'production' => array(
'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
'Archers' => array('time' => '1', 'cost' => '4'),
'Pegasi' => array('time' => '7', 'cost' => '16'),
)
);
$this->_castles[5] = array(
'name' => 'ARGENTHORN',
'income' => 22,
'defensePoints' => 4,
'position' => array('x' => 1680, 'y' => 1040),
'capital' => false,
'production' => array(
'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
'Archers' => array('time' => '1', 'cost' => '4'),
'Pegasi' => array('time' => '7', 'cost' => '16'),
)
);
$this->_castles[6] = array(
'name' => 'ANGBAR',
'income' => 20,
'defensePoints' => 4,
'position' => array('x' => 2160, 'y' => 880),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Cavalry' => array('time' => '4', 'cost' => '8'),
)
);
$this->_castles[7] = array(
'name' => 'SSURI',
'income' => 19,
'defensePoints' => 4,
'position' => array('x' => 2360, 'y' => 960),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Cavalry' => array('time' => '4', 'cost' => '8'),
)
);
$this->_castles[8] = array(
'name' => 'TROY',
'income' => 13,
'defensePoints' => 3,
'position' => array('x' => 2240, 'y' => 1080),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Cavalry' => array('time' => '4', 'cost' => '8'),
)
);
$this->_castles[9] = array(
'name' => 'HEREUTH',
'income' => 26,
'defensePoints' => 6,
'position' => array('x' => 2600, 'y' => 1400),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
'Navy' => array('time' => '13', 'cost' => '20'),
)
);
$this->_castles[10] = array(
'name' => 'GLUK',
'income' => 17,
'defensePoints' => 4,
'position' => array('x' => 3360, 'y' => 1680),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Giants' => array('time' => '3', 'cost' => '4'),
'Wolves' => array('time' => '3', 'cost' => '8'),
)
);
$this->_castles[11] = array(
'name' => 'GORK',
'income' => 15,
'defensePoints' => 4,
'position' => array('x' => 3440, 'y' => 1800),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Giants' => array('time' => '5', 'cost' => '4'),
'Wolves' => array('time' => '3', 'cost' => '8'),
)
);
$this->_castles[12] = array(
'name' => 'GAROM',
'income' => 20,
'defensePoints' => 4,
'position' => array('x' => 3480, 'y' => 1080),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Wolves' => array('time' => '3', 'cost' => '8'),
)
);
$this->_castles[13] = array(
'name' => 'BALAD NARAN',
'income' => 29,
'defensePoints' => 6,
'position' => array('x' => 3600, 'y' => 640),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
'Wolves' => array('time' => '2', 'cost' => '8'),
'Navy' => array('time' => '11', 'cost' => '20'),
)
);
$this->_castles[14] = array(
'name' => 'GALIN',
'income' => 20,
'defensePoints' => 4,
'position' => array('x' => 1640, 'y' => 0),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Cavalry' => array('time' => '8', 'cost' => '10'),
'Navy' => array('time' => '11', 'cost' => '20'),
)
);
$this->_castles[15] = array(
'name' => 'KOR',
'income' => 30,
'defensePoints' => 6,
'position' => array('x' => 4000, 'y' => 120),
'capital' => true,
'production' => array(
'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
'Giants' => array('time' => '5', 'cost' => '6'),
'Wolves' => array('time' => '3', 'cost' => '8'),
)
);
$this->_castles[16] = array(
'name' => 'DETHAL',
'income' => 20,
'defensePoints' => 4,
'position' => array('x' => 3560, 'y' => 0),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Wolves' => array('time' => '5', 'cost' => '8'),
)
);
$this->_castles[17] = array(
'name' => 'THURTZ',
'income' => 18,
'defensePoints' => 4,
'position' => array('x' => 3400, 'y' => 80),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Wolves' => array('time' => '3', 'cost' => '8'),
)
);
$this->_castles[18] = array(
'name' => 'DARCLAN',
'income' => 23,
'defensePoints' => 4,
'position' => array('x' => 3120, 'y' => 0),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Wolves' => array('time' => '3', 'cost' => '8'),
)
);
$this->_castles[19] = array(
'name' => 'ILNYR',
'income' => 21,
'defensePoints' => 4,
'position' => array('x' => 2840, 'y' => 240),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
'Navy' => array('time' => '11', 'cost' => '20'),
)
);
$this->_castles[20] = array(
'name' => 'DUINOTH',
'income' => 19,
'defensePoints' => 4,
'position' => array('x' => 2720, 'y' => 720),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Cavalry' => array('time' => '5', 'cost' => '8'),
)
);
$this->_castles[21] = array(
'name' => 'KAZRACK',
'income' => 21,
'defensePoints' => 4,
'position' => array('x' => 2320, 'y' => 120),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
'Navy' => array('time' => '11', 'cost' => '20'),
)
);
$this->_castles[22] = array(
'name' => 'VERNON',
'income' => 24,
'defensePoints' => 5,
'position' => array('x' => 1920, 'y' => 80),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Heavy Infantry' => array('time' => '3', 'cost' => '4'),
'Navy' => array('time' => '11', 'cost' => '20'),
)
);
$this->_castles[23] = array(
'name' => 'HIMELTON',
'income' => 14,
'defensePoints' => 3,
'position' => array('x' => 880, 'y' => 320),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Cavalry' => array('time' => '6', 'cost' => '8'),
)
);
$this->_castles[24] = array(
'name' => 'STORMHEIM',
'income' => 20,
'defensePoints' => 6,
'position' => array('x' => 760, 'y' => 800),
'capital' => true,
'production' => array(
'Giants' => array('time' => '2', 'cost' => '4'),
)
);
$this->_castles[25] = array(
'name' => 'OHMSMOUTH',
'income' => 24,
'defensePoints' => 5,
'position' => array('x' => 280, 'y' => 280),
'capital' => false,
'production' => array(
'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
'Navy' => array('time' => '10', 'cost' => '18'),
)
);
$this->_castles[26] = array(
'name' => 'WELLMORE',
'income' => 20,
'defensePoints' => 4,
'position' => array('x' => 160, 'y' => 640),
'capital' => false,
'production' => array(
'Heavy Infantry' => array('time' => '2', 'cost' => '4'),
'Navy' => array('time' => '11', 'cost' => '20'),
)
);
$this->_castles[27] = array(
'name' => 'TASME',
'income' => 19,
'defensePoints' => 4,
'position' => array('x' => 320, 'y' => 1000),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Navy' => array('time' => '11', 'cost' => '20'),
)
);
$this->_castles[28] = array(
'name' => 'VARDE',
'income' => 23,
'defensePoints' => 4,
'position' => array('x' => 320, 'y' => 1360),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Cavalry' => array('time' => '5', 'cost' => '8'),
'Navy' => array('time' => '11', 'cost' => '20'),
)
);
$this->_castles[29] = array(
'name' => 'QUIESCE',
'income' => 3,
'defensePoints' => 4,
'position' => array('x' => 600, 'y' => 1280),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Navy' => array('time' => '11', 'cost' => '20'),
)
);
$this->_castles[30] = array(
'name' => 'KHORFE',
'income' => 26,
'defensePoints' => 5,
'position' => array('x' => 320, 'y' => 1680),
'capital' => false,
'production' => array(
'Dwarves' => array('time' => '2', 'cost' => '4'),
'Griffins' => array('time' => '2', 'cost' => '4'),
)
);
$this->_castles[31] = array(
'name' => 'ALFAR\'S GAP',
'income' => 18,
'defensePoints' => 4,
'position' => array('x' => 880, 'y' => 2160),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
'Cavalry' => array('time' => '5', 'cost' => '8'),
)
);
$this->_castles[32] = array(
'name' => 'LADOR',
'income' => 16,
'defensePoints' => 4,
'position' => array('x' => 1240, 'y' => 720),
'capital' => false,
'production' => array(
'Light Infantry' => array('time' => '1', 'cost' => '4'),
)
);

    }

    public function getDefaultStartPositions() {
        return array(
            'white' => array(
                'id' => 0,
                'position' => $this->_castles[0]['position']
            ),
            'green' => array(
                'id' => 1,
                'position' => $this->_castles[1]['position']
            ),
            'red' => array(
                'id' => 15,
                'position' => $this->_castles[15]['position']
            ),
            'orange' => array(
                'id' => 24,
                'position' => $this->_castles[24]['position']
            )
        );
    }

    public function getCastlesSchema() {
        return $this->_castles;
    }

    static public function getBoardFields() {
        // x*y = 108*68 = 7344
        $fields[0] = array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'm', 'r', 'r', 'm', 'm', 'M', 'M', 'm', 'm', 'm', 'm', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'r', 'w', 'w', 'w', 'w', 'r', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'm', 'g', 'm', 'M', 'M', 'm', 'w', 'w', 'm', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'f', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g'
        );
        $fields[1] = array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'f', 'g', 'g', 'w', 'w', 'r', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'm', 'g', 'g', 'g', 'w', 'w', 'm', 'm', 'g', 'g', 'g', 'g', 'f', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'r', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'f', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'g'
        );
        $fields[2] = array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'f', 'r', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'w', 'w', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'r', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'g'
        );
        $fields[3] = array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'm', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'r', 'f', 'w', 'w', 'g', 'g', 'g', 'w', 'w', 'w', 'f', 'r', 'g', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'f', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'f', 'r', 'm', 'M', 'M', 'M', 'M', 'm', 'g'
        );
        $fields[4] = array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'g', 'm', 'm', 'g', 'g', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'g', 'w', 'w', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'w', 'w', 'w', 'r', 'f', 'g', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'm', 'g', 'g', 'g', 'g', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'r', 'f', 'm', 'M', 'M', 'M', 'M', 'm', 'g'
        );
        $fields[5] = array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'r', 'r', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'g', 'g', 'g', 'g', 'w', 'g', 'f', 'w', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'g', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'g'
        );
        $fields[6] = array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'f', 'M', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'r', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'f', 'r', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g'
        );
        $fields[7] = array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'f', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'm', 'm', 'g', 'w', 'w', 'f', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'r', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'r', 'f', 'g', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g'
        );
        $fields[8] = array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'r', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'f', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'm', 'g', 'w', 'g', 'g', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g'
        );
        $fields[9] = array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'r', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'r', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g'
        );
        $fields[10] = array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'g', 'g', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'r', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'm', 'g', 'g'
        );
        $fields[11] = array(
            'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'f', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'f', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'f', 'f', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'm', 'g', 'g'
        );
        $fields[12] = array(
            'w', 'w', 'w', 'w', 'w', 'f', 'w', 'w', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'M', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'r', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'f', 'f', 'g', 'g', 'r', 'r', 'r', 'f', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'm', 'M', 'M', 'm', 'g', 'g'
        );
        $fields[13] = array(
            'w', 'w', 'w', 'w', 'w', 'f', 'w', 'w', 'w', 'f', 'g', 'g', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'f', 'f', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'w', 'w', 'r', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'm', 'M', 'M', 'm', 'g', 'g'
        );
        $fields[14] = array(
            'w', 'w', 'w', 'w', 'w', 'f', 'w', 'w', 'w', 'g', 'g', 'r', 'r', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'r', 'm', 'g', 'g', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'r', 'g', 'r', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'g', 'm', 'm', 'm', 'm', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'w', 'w', 'w', 'r', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'm', 'g', 'g'
        );
        $fields[15] = array(
            'w', 'w', 'w', 'w', 'g', 'g', 'r', 'r', 'r', 'r', 'r', 'r', 'f', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'f', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'm', 'g', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'r', 'g', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'g'
        );
        $fields[16] = array(
            'w', 'w', 'w', 'w', 'f', 'r', 'r', 'w', 'w', 'w', 'g', 'r', 'f', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'm', 'm', 'm', 'm', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'f', 'f', 'r', 'r', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'g'
        );
        $fields[17] = array(
            'w', 'w', 'w', 'w', 'r', 'f', 'g', 'w', 'w', 'w', 'w', 'r', 'f', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'm', 'm', 'm', 'g', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'r', 'f', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'g'
        );
        $fields[18] = array(
            'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'w', 'w', 'w', 'r', 'f', 'f', 'f', 'f', 'f', 'r', 'r', 'r', 'm', 'm', 'm', 'f', 'f', 'g', 'm', 'm', 'm', 'g', 'g', 'f', 'r', 'r', 'g', 'g', 'g', 'f', 'f', 'f', 'w', 'w', 'w', 'w', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'f', 'r', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'g'
        );
        $fields[19] = array(
            'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'f', 'w', 'w', 'r', 'r', 'f', 'f', 'f', 'f', 'm', 'm', 'r', 'm', 'm', 'm', 'f', 'f', 'm', 'm', 'm', 'g', 'g', 'g', 'r', 'f', 'r', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'w', 'g', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'r', 'f', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'm', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'g', 'g'
        );
        $fields[20] = array(
            'w', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'r', 'f', 'f', 'f', 'f', 'm', 'm', 'f', 'r', 'm', 'm', 'f', 'f', 'm', 'm', 'g', 'g', 'g', 'g', 'f', 'f', 'r', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'w', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'g', 'g'
        );
        $fields[21] = array(
            'w', 'w', 'w', 'w', 'g', 'f', 'f', 'g', 'g', 'w', 'w', 'w', 'r', 'f', 'f', 'f', 'f', 'm', 'm', 'r', 'f', 'm', 'm', 'f', 'f', 'm', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'r', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'g', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'f', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g'
        );
        $fields[22] = array(
            'w', 'w', 'w', 'w', 'f', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'g', 'f', 'f', 'f', 'm', 'm', 'm', 'm', 'm', 'm', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'f', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'm', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'M', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g'
        );
        $fields[23] = array(
            'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'g', 'f', 'f', 'f', 'm', 'g', 'm', 'm', 'g', 'g', 'f', 'f', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'r', 'r', 'f', 'f', 'f', 'w', 'w', 'g', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'g', 'g', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'w', 'g', 'g', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g'
        );
        $fields[24] = array(
            'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'g', 'g', 'f', 'f', 'f', 'f', 'm', 'g', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'f', 'r', 'w', 'w', 'r', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'w', 'w', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g'
        );
        $fields[25] = array(
            'w', 'w', 'w', 'w', 'f', 'f', 'g', 'g', 'f', 'r', 'w', 'w', 'r', 'r', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'f', 'g', 'g', 'r', 'f', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'g', 'w', 'w', 'w', 'w', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'g'
        );
        $fields[26] = array(
            'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'r', 'f', 'w', 'w', 'w', 'r', 'r', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'g'
        );
        $fields[27] = array(
            'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'r', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'r', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'f', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g'
        );
        $fields[28] = array(
            'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'w', 'r', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'f', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'f', 'r', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'g', 'g'
        );
        $fields[29] = array(
            'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'w', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'g', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'g', 'g'
        );
        $fields[30] = array(
            'w', 'w', 'w', 'f', 'f', 'f', 'g', 'g', 'w', 'w', 'w', 'M', 'w', 'w', 'w', 'r', 'r', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'w', 'w', 'g', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'f', 'f', 'r', 'g', 'g', 'g', 'g', 'w', 'w', 'r', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g'
        );
        $fields[31] = array(
            'w', 'w', 'w', 'f', 'f', 'f', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'g', 'r', 'r', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'M', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'r', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'w', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g'
        );
        $fields[32] = array(
            'w', 'w', 'w', 'f', 'f', 'f', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'w', 'w', 'f', 'r', 'r', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'g'
        );
        $fields[33] = array(
            'w', 'w', 'w', 'f', 'f', 'f', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'g', 'r', 'f', 'r', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'm', 'g', 'm', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'g'
        );
        $fields[34] = array(
            'w', 'w', 'w', 'f', 'f', 'f', 'g', 'g', 'f', 'r', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'f', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'g', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'w', 'w', 'g', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'g'
        );
        $fields[35] = array(
            'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'r', 'f', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'm', 'm', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'r', 'f', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'f', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'f', 'f', 'f', 'f', 'M', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g'
        );
        $fields[36] = array(
            'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'r', 'f', 'r', 'g', 'g', 'g', 'g', 'g', 'r', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'f', 'g', 'g', 'g', 'g', 'g', 'r', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g'
        );
        $fields[37] = array(
            'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'f', 'f', 'f', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g'
        );
        $fields[38] = array(
            'w', 'w', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'r', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'w', 'w', 'f', 'f', 'r', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'r', 'g', 'r', 'g', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g'
        );
        $fields[39] = array(
            'w', 'w', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'r', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'r', 'r', 'r', 'M', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'r', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'g', 'r', 'g', 'f', 'f', 'f', 'f', 'f', 'm', 'm', 'M', 'M', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'g', 'g'
        );
        $fields[40] = array(
            'w', 'g', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'r', 'r', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'f', 'f', 'f', 'g', 'g', 'g', 'r', 'r', 'r', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'w', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'g', 'r', 'g', 'f', 'f', 'f', 'f', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g'
        );
        $fields[41] = array(
            'm', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'g', 'w', 'w', 'w', 'r', 'g', 'g', 'g', 'm', 'g', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'm', 'm', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'f', 'f', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'f', 'f', 'g', 'g'
        );
        $fields[42] = array(
            'm', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'f', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'w', 'w', 'w', 'w', 'r', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'r', 'w', 'w', 'w', 'w', 'g', 'm', 'M', 'M', 'M', 'M', 'm', 'f', 'r', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'f', 'f', 'f', 'g', 'g'
        );
        $fields[43] = array(
            'm', 'm', 'M', 'M', 'M', 'm', 'm', 'm', 'r', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'w', 'w', 'w', 'w', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'm', 'M', 'M', 'M', 'M', 'm', 'r', 'f', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'f', 'f', 'f', 'f', 'g', 'g'
        );
        $fields[44] = array(
            'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'm', 'M', 'M', 'M', 'M', 'm', 'g', 'r', 'r', 'r', 'r', 'r', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'f', 'f', 'f', 'f', 'f', 'g', 'g'
        );
        $fields[45] = array(
            'g', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'r', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'g', 'f', 'r', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g'
        );
        $fields[46] = array(
            'g', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'r', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'm', 'm', 'M', 'M', 'm', 'm', 'm', 'r', 'f', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'f', 'f', 'f', 'f', 'M', 'g', 'g', 'g'
        );
        $fields[47] = array(
            'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'r', 'm', 'm', 'm', 'm', 'g', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[48] = array(
            'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[49] = array(
            'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'r', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[50] = array(
            'g', 'g', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'r', 'g', 'g', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[51] = array(
            'g', 'g', 'g', 'm', 'm', 'M', 'f', 'f', 'f', 'f', 'f', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'r', 'g', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'r', 'g', 'g', 'g', 'g', 'r', 'r', 'm', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'w', 'w', 'w', 'w', 'g', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[52] = array(
            'g', 'g', 'g', 'g', 'm', 'g', 'm', 'm', 'm', 'M', 'f', 'f', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'r', 'r', 'r', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'm', 'w', 'w', 'w', 'w', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[53] = array(
            'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'f', 'f', 'f', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'r', 'r', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'm', 'w', 'w', 'w', 'w', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[54] = array(
            'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'f', 'f', 'f', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'f', 'r', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'm', 'm', 'r', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'r', 'm', 'm', 'M', 'M', 'm', 'w', 'w', 'w', 'w', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[55] = array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'm', 'm', 'M', 'f', 'f', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'r', 'f', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'm', 'g', 'r', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'm', 'w', 'w', 'w', 'w', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[56] = array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'f', 'f', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'r', 'w', 'w', 'f', 'r', 'g', 'f', 'f', 'f', 'f', 'm', 'M', 'M', 'm', 'w', 'w', 'w', 'w', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[57] = array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'f', 'f', 'f', 'f', 'f', 'w', 'w', 'f', 'f', 'f', 'f', 'f', 'f', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'g', 'r', 'w', 'g', 'r', 'r', 'g', 'f', 'f', 'f', 'f', 'g', 'M', 'M', 'm', 'w', 'w', 'w', 'w', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[58] = array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'f', 'f', 'm', 'm', 'r', 'w', 'w', 'r', 'f', 'f', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'r', 'r', 'r', 'r', 'g', 'g', 'g', 'f', 'g', 'g', 'g', 'M', 'M', 'm', 'w', 'w', 'w', 'g', 'm', 'M', 'M', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[59] = array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'w', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'M', 'M', 'm', 'g', 'w', 'g', 'g', 'm', 'M', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[60] = array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'M', 'M', 'M', 'M', 'm', 'm', 'M', 'g', 'm', 'm', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[61] = array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'm', 'm', 'M', 'M', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'M', 'M', 'M', 'M', 'g', 'g', 'M', 'M', 'M', 'M', 'M', 'M', 'g', 'g', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[62] = array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'g', 'g', 'g', 'M', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[63] = array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[64] = array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'm', 'M', 'M', 'M', 'm', 'g', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[65] = array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'm', 'm', 'g', 'g', 'm', 'm', 'm', 'm', 'M', 'M', 'm', 'm', 'g', 'g', 'm', 'm', 'M', 'M', 'M', 'm', 'm', 'm', 'm', 'g', 'g', 'm', 'm', 'm', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'M', 'M', 'M', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'M', 'M', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[66] = array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'g', 'g', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'g', 'm', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'M', 'M', 'M', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[67] = array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'm', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );
        $fields[68] = array(
            'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g', 'g'
        );

        return $fields;
    }

    public function getCastle($castleId) {
        return $this->_castles[$castleId];
    }

    static public function getTerrain($type) {
        switch($type) {
            case 'r':
                return array('Road', 1);
            case 'w':
                return array('Water', 10);
            case 'm':
                return array('Hills', 5);
            case 'M':
                return array('Mountains', 10);
            case 'g':
                return array('Grassland', 2);
            case 'f':
                return array('Forest', 3);
            case 's':
                return array('Swamp', 4);
        }
    }


    static public function production() {
        return '
#CITY(Defense Value) CAPITAL      ARMY TYPES PERSENT (Time/Cost/Strength/Movement)
DESERTION    (4)    -            Light Infantry    (1/4/3/10)  Navy    (11/20/5/18)
ZAIGONNE     (4)    -            Light Infantry    (1/4/3/10)  Navy    (11/20/5/18)
BERERI       (6)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
                                 Cavalry   (6/8/6/16)  Navy    (11/20/5/18)
TAL          (4)    -            Light Infantry    (1/4/3/10)  Navy    (11/20/5/18)
MINBOURNE    (4)    -            Light Infantry    (1/4/3/10)  Navy    (11/20/4/18)
TIRFING      (4)    -            Light Infantry     (2/4/4/9)
AMENAL       (4)    -            Light Infantry    (1/4/3/10)
PARETH       (5)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
                                 Cavalry   (5/8/5/14)
UNGOR        (4)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
GORAG        (4)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
VIVAL        (3)    -            Light Infantry    (1/4/3/10)
BANE CTIADEL (6)    Lord Bane    Giants    (4/4/6/10)  Light Infantry    (1/4/3/10)
                                 Cavalry   (4/8/6/16)  Wolves    (3/8/5/14)
NEEDLETON    (4)    -            Light Infantry    (1/4/3/10)
GUNTHANG     (5)    -            Heavy Infantry    (2/4/5/8)  Archers   (3/4/4/12)
                                 Pegasi   (5/16/4/16)
DEEPHALLOW   (4)    -            Light Infantry    (1/4/3/10)
AK-GIRIEL    (4)    -            Heavy Infantry    (2/4/5/8)  Archers   (3/4/4/12)
                                 Pegasi   (7/16/4/16)
AK-ENLIE     (4)    -            Heavy Infantry    (2/4/5/8)  Archers   (3/4/4/12)
AK-FARZON    (4)    -            Heavy Infantry    (2/4/5/8)  Archers   (3/4/4/12)
MENELOTH     (5)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
                                 Cavalry   (5/8/5/15)
MALIKOR      (4)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
                                 Cavalry   (6/8/6/13)
ENMOUTH      (6)    Selentines   Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
                                 Cavalry   (4/8/6/20)  Navy     (8/20/6/18)
ARGROND      (4)    -            Heavy Infantry    (2/4/6/7)  Light Infantry    (1/4/3/10)
BARTHEL      (5)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
MARTON       (4)    -            Heavy Infantry    (2/4/4/8)  Light Infantry    (1/4/3/10)
                                 Navy    (11/20/5/18)
WAYBOURNE    (4)    -            Heavy Infantry    (2/4/5/8)  Navy    (11/20/5/18)
DETHAL       (4)    -            Light Infantry    (1/4/3/10)  Wolves    (5/8/5/14)
GREENWEIGH   (4)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
                                 Dwarves    (3/4/5/9)
UPWAY        (3)    -            Light Infantry    (2/4/3/10)
DARCLAN      (4)    -            Light Infantry    (1/4/3/10)  Wolves    (3/8/5/14)
MARIDUN      (4)    -            Light Infantry    (2/4/3/10)  Cavalry   (3/8/6/18)
JESSARTON    (4)    -            Light Infantry    (3/4/2/10)  Navy    (12/20/3/18)
ZHORAN       (5)    -            Dwarves    (1/4/5/9)  Griffins (5/16/5/16)
KHAMAR       (6)    Dwarves      Dwarves    (2/4/5/9)  Griffins (5/16/6/18)
UPBOURNE     (4)    -            Heavy Infantry    (2/4/5/8)  Navy    (11/20/5/18)
CRAGMORTON   (4)    -            Dwarves    (2/4/5/9)  Griffins (7/16/5/16)
PAYNOR       (3)    -            Light Infantry    (2/4/3/10)
CARMEL       (5)    -            Light Infantry    (2/4/5/12)
DUNETHAL     (6)    Horse Lords  Light Infantry    (1/4/3/10)  Cavalry   (3/6/6/19)
DERRIDON     (4)    -            Light Infantry     (2/4/3/9)  Cavalry   (4/8/6/18)
FLEYMARK     (4)    -            Light Infantry    (1/4/3/10)  Cavalry   (3/6/6/18)
PA-KUR       (5)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
                                 Cavalry   (4/8/6/16)
AR_ARAK      (5)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
                                 Cavalry   (4/8/6/16)
DHAR-KHOSIS  (6)    -            Heavy Infantry    (2/4/5/8)  Dwarves    (2/4/5/9)
                                 Griffins (6/16/5/16)
CIT OF FIRE  (5)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
                                 Navy    (11/20/5/18)
CIT OF ICE   (5)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
                                 Navy    (11/20/5/18)
HITHOS       (6)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
                                 Cavalry   (5/8/6/16)  Navy    (10/20/5/20)
UBAR         (4)    -            Light Infantry    (1/4/3/10)  Navy    (11/20/5/18)
GALIN        (4)    -            Light Infantry    (1/4/3/10)  Cavalry  (8/10/6/16)
                                 Navy    (11/20/5/18)
VERNON       (5)    -            Heavy Infantry    (3/4/5/7)  Light Infantry    (1/4/3/10)
                                 Navy    (11/20/6/18)
THURTZ       (4)    -            Light Infantry    (1/4/3/10)  Wolves    (3/8/5/14)
KAZRACK      (4)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
                                 Navy    (11/20/5/18)
KOR          (6)    Orcs of Kor  Giants    (5/6/6/10)  Heavy Infantry    (2/4/5/8)
                                 Wolves    (3/8/5/14)
ILNYR        (4)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
                                 Navy    (11/20/5/18)
OHMSMOUTH    (5)    -            Heavy Infantry    (2/4/5/8)  Navy    (10/18/5/18)
HIMELTON     (3)    -            Light Infantry    (1/4/3/10)  Cavalry   (6/8/5/16)
WELLMORE     (4)    -            Heavy Infantry    (2/4/5/8)  Navy    (11/20/5/18)
BALAD NARAN  (6)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
                                 Navy    (11/20/5/18)  Wolves    (2/8/6/15)
LADOR        (4)    -            Light Infantry    (1/4/3/10)
DUINOTH      (4)    -            Light Infantry    (1/4/3/10)  Cavalry   (5/8/6/16)
STORMHEIM    (6)    Storm Giants Giants    (2/4/6/12)
ANGBAR       (4)    -            Light Infantry    (1/4/3/10)  Cavalry   (4/8/5/16)
SSURI        (4)    -            Light Infantry    (1/4/3/10)  Cavalry   (4/8/5/16)
TASME        (4)    -            Light Infantry    (1/4/3/10)  Navy    (11/20/5/18)
ARGENTHORN   (4)    -            Heavy Infantry    (2/4/5/8)  Archers   (1/4/4/12)
                                 Pegasi   (7/16/4/16)
TROY         (3)    -            Light Infantry    (1/4/3/10)  Cavalry   (4/8/6/15)
GAROM        (4)    -            Light Infantry    (1/4/3/10)  Wolves    (3/8/5/14)
QUIESCE      (4)    -            Light Infantry    (1/4/3/10)  Navy    (11/20/5/18)
VARDE        (4)    -            Light Infantry    (1/4/3/10)  Cavalry   (5/8/5/16)
                                 Navy    (11/20/5/18)
CHARLING     (3)    -            Light Infantry    (1/4/3/10)
HEREUTH      (6)    -            Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
                                 Navy    (13/20/5/18)
ELVALLIE     (6)    Elvallie     Heavy Infantry   (2/4/5/10)  Archers   (1/4/4/12)
                                 Pegasi   (6/16/4/16)
GILDENHOME   (5)    -            Heavy Infantry    (2/4/5/8)  Archers   (1/4/4/12)
                                 Pegasi   (7/16/4/16)
KHORFE       (5)    -            Dwarves    (2/4/5/9)  Griffins (8/16/5/16)
GLUK         (4)    -            Giants    (3/4/6/10)  Light Infantry    (1/4/3/10)
                                 Wolves    (3/8/5/14)
LOREMARK     (4)    -            Heavy Infantry    (2/4/5/8)  Archers   (1/4/4/12)
                                 Pegasi   (7/16/4/16)
GORK         (4)    -            Giants     (5/4/5/8)  Light Infantry    (1/4/3/10)
                                 Wolves    (3/8/5/14)
ALFAR\'S GAP  (4)    -            Light Infantry    (1/4/3/10)  Cavalry   (5/8/5/16)
MARTHOS      (6)    Sirians      Heavy Infantry    (2/4/5/8)  Light Infantry    (1/4/3/10)
                                 Cavalry   (4/8/6/16)  Pegasi   (7/16/5/15)
';
    }

}
