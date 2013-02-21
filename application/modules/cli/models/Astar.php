<?php

/**
 * A* search algorithm implemantation.
 */
class Cli_Model_Astar extends Cli_Model_Heuristics {

    /**
     * The set of nodes already evaluated.
     *
     * @var array
     */
    private $close = array();

    /**
     * The set of tentative nodes to be evaluated
     *
     * @var array
     */
    private $open = array();

    /**
     * Number of loops
     *
     * @var int
     */
    private $nr = 0;

    /**
     * Shortest path
     *
     * @var array
     */
    private $path = array();

    /**
     * All map fields
     *
     * @var array
     */
    private $fields;
    private $terrainCosts;
    private $movesLeft;

    /**
     * Current position on path
     *
     * @var array
     */
    private $currentPosition;

    /**
     * Constructor
     *
     * @param int $destX
     * @param int $destY
     * @param array $fields
     */
    public function __construct($army, $destX, $destY, $fields) {
        parent::__construct($destX, $destY);
        if ($army['x'] == $this->destX && $army['y'] == $this->destY) {
            return null;
        }
        $this->fields = $fields;
        $this->terrainCosts = $army['terrainCosts'];
        $this->movesLeft = $army['movesLeft'];

        $this->open[$army['x'] . '_' . $army['y']] = $this->node($army['x'], $army['y'], 0, null);
        $this->aStar();
    }

    /**
     * A* algorithm
     *
     * @throws Exception on too many loops
     * @return bool
     */
    private function aStar() {
        $this->nr++;
        if ($this->nr > 30000) {
            $this->nr--;
//            throw new Exception(Zend_Debug::dump($this->close));
            throw new Exception('>' + $this->nr);
        }
        $key = $this->findSmallestF();
        $x = $this->open[$key]['x'];
        $y = $this->open[$key]['y'];
        $this->close[$key] = $this->open[$key];
        unset($this->open[$key]);
        $this->addOpen($x, $y);
        if ($x == $this->destX && $y == $this->destY) {
            return true;
        }
        if (!$this->isNotEmpty()) {
            return null;
//            throw new Exception('Nie znalazłem ścieżki');
        }
        $this->aStar();
    }

    /**
     * Counts open set
     *
     * @return int
     */
    private function isNotEmpty() {
        return count($this->open);
    }

    /**
     * Finds smallest cost to goal
     *
     * @return int
     */
    private function findSmallestF() {
        $i = 0;
        foreach ($this->open as $k => $v)
        {
            if (!isset($this->open[$i])) {
                $i = $k;
            }
            if ($this->open[$k]['F'] < $this->open[$i]['F']) {
                $i = $k;
            }
        }
        return $i;
    }

    /**
     * Adds node to open set
     *
     * @param int $x
     * @param int $y
     */
    private function addOpen($x, $y) {
        $startX = $x - 1;
        $startY = $y - 1;
        $endX = $x + 1;
        $endY = $y + 1;
        for ($i = $startX; $i <= $endX; $i++)
        {
            for ($j = $startY; $j <= $endY; $j++)
            {
                if ($x == $i && $y == $j) {
                    continue;
                }

                $key = $i . '_' . $j;

                if (isset($this->close[$key]) && $this->close[$key]['x'] == $i && $this->close[$key]['y'] == $j) {
                    continue;
                }

                // jeśli na mapie nie ma tego pola to pomiń to pole
                if (!isset($this->fields[$j][$i])) {
                    continue;
                }

                $type = $this->fields[$j][$i];

                // jeżeli na polu znajduje się wróg to pomiń to pole
                if ($type == 'e') {
                    continue;
                }

//                $terrain = Application_Model_Board::getTerrain($type, $this->canFly, $this->canSwim);
//                $g = $terrain[1];

                $g = $this->terrainCosts[$type];

                // jeżeli koszt ruchu większy od pozostałych puktów ruchu to pomiń to pole
                if ($g > $this->movesLeft) {
                    continue;
                }

                if (isset($this->open[$key])) {
                    $this->calculatePath($x . '_' . $y, $g, $key);
                } else {
                    $parent = array(
                        'x' => $x,
                        'y' => $y
                    );
                    $g += $this->close[$x . '_' . $y]['G'];
                    $this->open[$key] = $this->node($i, $j, $g, $parent);
                }
            }
        }
    }

    /**
     * Calculates path cost
     *
     * @param string $kA
     * @param int $g
     * @param string $key
     */
    private function calculatePath($kA, $g, $key) {
        if ($this->open[$key]['G'] > ($g + $this->close[$kA]['G'])) {
            $this->open[$key]['parent'] = array(
                'x' => $this->close[$kA]['x'],
                'y' => $this->close[$kA]['y']
            );
            $this->open[$key]['G'] = $g + $this->close[$kA]['G'];
            $this->open[$key]['F'] = $this->open[$key]['G'] + $this->open[$key]['H'];
        }
    }

    /**
     *
     * @param type $x
     * @param type $y
     * @param type $g
     * @param type $parent
     * @return type
     */
    private function node($x, $y, $g, $parent) {
        $h = $this->calculateH($x, $y);
        return array(
            'x' => $x,
            'y' => $y,
            'G' => $g,
            'H' => $h,
            'F' => $h + $g,
            'parent' => $parent
        );
    }

    /**
     *
     * @param type $key
     * @return null
     */
    public function restoreFullPath($key) {
        if (!isset($this->close[$key])) {
            new Cli_Model_Logger('Nie ma takiego klucza: ' . $key . ' w ścieżce');
            return null;
        }
        while (!empty($this->close[$key]['parent']))
        {
            $path[] = array(
                'x' => $this->close[$key]['x'],
                'y' => $this->close[$key]['y']);
        }
        $path = array_reverse($path);
        return $path;
    }

    /**
     *
     * @param type $key
     * @return null
     */
    public function getFullPathMovesSpend($key) {
        if (!isset($this->close[$key])) {
            new Cli_Model_Logger('Nie ma takiego klucza: ' . $key . ' w ścieżce');
            return null;
        }
        return $this->close[$key]['G'];
    }

    /**
     *
     *
     * @param string $key
     * @param type $moves
     * @return int
     */
    public function getPath($key, $moves) {
//        throw new Exception(Zend_Debug::dump($this->close));
        if (!isset($this->close[$key])) {
            new Cli_Model_Logger('W ścieżce nie ma podanego jako parametr klucza: ' . $key);
            return 0;
        }
        $this->currentPosition;
//         $currentPosition = array(
//             'x' => $this->close[$key]['x'],
//             'y' => $this->close[$key]['y'],
//             'movesSpend' => $this->close[$key]['G']);
        while (!empty($this->close[$key]['parent']))
        {
//            throw new Exception(Zend_Debug::dump($this->close));
            if ($this->close[$key]['G'] <= $moves) {
                if (!$this->currentPosition) {
                    $this->currentPosition = array(
                        'x' => $this->close[$key]['x'],
                        'y' => $this->close[$key]['y'],
                        'movesSpend' => $this->close[$key]['G']);
                }
                $this->path[] = array(
                    'x' => $this->close[$key]['x'],
                    'y' => $this->close[$key]['y']);
            }
            $key = $this->close[$key]['parent']['x'] . '_' . $this->close[$key]['parent']['y'];
        }
        $this->path[] = array(
            'x' => $this->close[$key]['x'],
            'y' => $this->close[$key]['y']);
        $this->path = array_reverse($this->path);
//         if (!$this->currentPosition) {
//             $this->currentPosition = $currentPosition;
//         }
        unset($this->path[0]);
        return $this->path;
    }

    /**
     * Getter for currentPosition
     *
     * @return array
     */
    public function getCurrentPosition() {
        return $this->currentPosition;
    }

    /**
     * Reverse path
     *
     * @return array
     */
    public function reversePath() {
        $this->currentPosition = array(
            'x' => $this->path[1]['x'],
            'y' => $this->path[1]['y'],
            'movesSpend' => $this->close[$this->path[1]['x'] . '_' . $this->path[1]['y']]['G']);
        $this->path = array_reverse($this->path);
        return $this->path;
    }

}

