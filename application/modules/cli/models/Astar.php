<?php

/**
 * A* search algorithm implementation.
 */
class Cli_Model_Astar extends Cli_Model_Heuristics
{

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
     * All map fields
     *
     * @var array
     */
    private $fields;
    private $terrainCosts;
    private $movesLeft;
    private $limit;


    /**
     * Constructor
     *
     * @param int $destX
     * @param int $destY
     * @param array $fields
     */
    public function __construct($army, $destX, $destY, $fields, $limit = null)
    {
        if ($army['x'] == $destX && $army['y'] == $destY) {
            return;
        }
        parent::__construct($destX, $destY);
        $this->limit = $limit;
        $this->fields = $fields;
        $this->terrainCosts = $army['terrainCosts'];
        $this->movesLeft = $army['movesLeft'];

        $this->open[$army['x'] . '_' . $army['y']] = $this->node($army['x'], $army['y'], 0, null, 'c');
        $this->aStar();
    }

    /**
     * A* algorithm
     *
     * @throws Exception on too many loops
     * @return bool
     */
    private function aStar()
    {
        $this->nr++;
        if ($this->nr > 30000) {
            $this->nr--;
            throw new Exception('>' + $this->nr);
        }
        $key = $this->findSmallestF();
        $x = $this->open[$key]['x'];
        $y = $this->open[$key]['y'];
        $this->close[$key] = $this->open[$key];
        if ($x == $this->destX && $y == $this->destY) {
            return true;
        }
        unset($this->open[$key]);
        $this->addOpen($x, $y);
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
    private function isNotEmpty()
    {
        return count($this->open);
    }

    /**
     * Finds smallest cost to goal
     *
     * @return int
     */
    private function findSmallestF()
    {
        $i = 0;
        foreach (array_keys($this->open) as $k) {
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
     * Adds nodes which are around $x,$y to the open set
     *
     * @param int $x
     * @param int $y
     */
    private function addOpen($x, $y)
    {
        $startX = $x - 1;
        $startY = $y - 1;
        $endX = $x + 1;
        $endY = $y + 1;
        for ($i = $startX; $i <= $endX; $i++) {
            for ($j = $startY; $j <= $endY; $j++) {

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

                $terrainType = $this->fields[$j][$i];

                // jeżeli na polu znajduje się wróg to pomiń to pole
                if ($terrainType == 'e') {
                    continue;
                }

                $g = $this->terrainCosts[$terrainType];

                // jeżeli koszt ruchu większy od 99 to pomiń to pole
                if ($g > 6) {
                    continue;
                }

                if (isset($this->open[$key])) {
                    $this->calculatePath($x . '_' . $y, $g, $key);
                } else {
                    $g += $this->close[$x . '_' . $y]['G'];
                    // pomiń jeśli koszt ścieżki jest większy od pozostałych ruchów
                    if ($this->limit && $g > $this->movesLeft) {
                        continue;
                    }
                    $parent = array(
                        'x' => $x,
                        'y' => $y
                    );
                    $this->open[$key] = $this->node($i, $j, $g, $parent, $terrainType);
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
    private function calculatePath($kA, $g, $key)
    {
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
    private function node($x, $y, $g, $parent, $terrainType)
    {
        $h = $this->calculateH($x, $y);
        return array(
            'x' => $x,
            'y' => $y,
            'G' => $g,
            'H' => $h,
            'F' => $h + $g,
            'parent' => $parent,
            'tt' => $terrainType
        );
    }

    /**
     *
     *
     * @param string $key
     * @param type $moves
     * @return int
     */
    public function getPath($key)
    {
        if (!isset($this->close[$key])) {
            $l = new Coret_Model_Logger();
            $l->log('W ścieżce nie ma podanego jako parametr klucza: ' . $key . ' (getPath)');
            return;
        }
        $path = array();
        while (!empty($this->close[$key]['parent'])) {
            $path[] = $this->close[$key];
            $key = $this->close[$key]['parent']['x'] . '_' . $this->close[$key]['parent']['y'];
        }
        $path = array_reverse($path);
        return $path;
    }

    /**
     *
     *
     * @param string $key
     * @param type $moves
     * @return int
     */
    public function getReturnPath($key)
    {
        if (!isset($this->close[$key])) {
            $l = new Coret_Model_Logger();
            $l->log('W ścieżce nie ma podanego jako parametr klucza: ' . $key . ' (getPath)');
            return;
        }
        $path = array();
        while (!empty($this->close[$key]['parent'])) {
            $path[] = $this->close[$key];
            $key = $this->close[$key]['parent']['x'] . '_' . $this->close[$key]['parent']['y'];
        }
        return $path;
    }

}

