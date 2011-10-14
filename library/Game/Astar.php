<?php

class Game_Astar {

    private $close = array();
    private $open = array();
    private $destX;
    private $destY;
    private $nr = 0;
    private $path = array();
    private $fields;
    private $canFly;
    private $canSwim;

    public function __construct($srcX, $srcY, $destX, $destY, $fields, $canFly, $canSwim, $moves) {
        $this->destX = $destX;
        $this->destY = $destY;
        $this->fields = $fields;
        $this->canFly = $canFly;
        $this->canSwim = $canSwim;
        $this->open[$srcX . '_' . $srcY] = $this->node($srcX, $srcY, 0, null);
        $this->aStar();
        return restorePath($destX . '_' . $destY, $moves);
    }

    private function aStar() {
        $this->nr++;
        if ($this->nr > 30000) {
            $this->nr--;
            throw new Exception('>' + $this->nr);
        }
        $key = $this->findSmallestF();
        $x = $this->open[$key][x];
        $y = $this->open[$key][y];
        $this->close[$key] = $this->open[$key];
        unset($this->open[$key]);
        $this->addOpen($x, $y);
        if ($x == $this->destX && $y == $this->destY) {
            return true;
        }
        if (!$this->isNotEmpty()) {
            throw new Exception('Nie znalazłem ścieżki');
        }
        $this->aStar();
    }

    private function isNotEmpty() {
        return count($this->open);
    }

    private function findSmallestF() {
        $i;
        foreach ($this->open as $k => $v) {
            if (!isset($this->open[$i])) {
                $i = $k;
            }
            if ($this->open[$i]['F'] < $this->open[$k]['F']) {
                $i = $k;
            }
        }
        return $i;
    }

    private function addOpen($x, $y) {
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
                if (isset($fields[$j][$i])) {
                    continue;
                }
                $terrain = Application_Model_Board::getTerrain($this->fields[$j][$i], $this->canFly, $this->canSwim);
                $g = $terrain[1];
                if ($g > 5) {
                    continue;
                }
                if (isset($this->open[$key])) {
                    $this->calculatePath($x . '_' . $y, $g, $key);
                } else {
                    $parent = array('x' => $x,
                        'y' => $y);
                    $g += $this->close[$x . '_' . $y]['G'];
                    $this->open[$key] = $this->node($i, $j, $g, $parent);
                }
            }
        }
    }

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

    private function calculateH($x, $y) {
        $h = 0;
        $xLengthPoints = $x - $this->destX;
        $yLengthPoints = $y - $this->destY;
        if ($xLengthPoints < $yLengthPoints) {
            for ($i = 1; $i <= $xLengthPoints; $i++) {
                $h++;
            }
            for ($i = 1; $i <= ($yLengthPoints - $xLengthPoints); $i++) {
                $h++;
            }
        } else {
            for ($i = 1; $i <= $yLengthPoints; $i++) {
                $h++;
            }
            for ($i = 1; $i <= ($xLengthPoints - $yLengthPoints); $i++) {
                $h++;
            }
        }
        return $h;
    }

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

    public function restorePath($key, $moves) {
        if (!isset($this->close[$key])) {
            return 0;
        }
        while (!empty($this->close[$key]['parent'])) {
            if ($this->close[$key]['G'] <= $moves) {
                $this->path . push(array('x' => $this->close[$key]['x'], 'y' => $this->close[$key]['y']));
            }
            $key = $this->close[$key]['parent']['x'] . '_' . $this->close[$key]['parent']['y'];
        }
    }

}

