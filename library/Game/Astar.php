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
    private $currentPosition;

    public function __construct($destX, $destY) {
        $this->destX = $destX;
        $this->destY = $destY;
    }

    public function start($srcX, $srcY, $fields, $canFly, $canSwim) {
        if ($srcX == $this->destX && $srcY == $this->destY) {
            return null;
        }
        $this->fields = $fields;
        $this->canFly = $canFly;
        $this->canSwim = $canSwim;
        $this->open[$srcX . '_' . $srcY] = $this->node($srcX, $srcY, 0, null);
        $this->aStar();
    }

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
            throw new Exception('Nie znalazłem ścieżki');
        }
        $this->aStar();
    }

    private function isNotEmpty() {
        return count($this->open);
    }

    private function findSmallestF() {
        $i = 0;
        foreach ($this->open as $k => $v) {
            if (!isset($this->open[$i])) {
                $i = $k;
            }
            if ($this->open[$k]['F'] < $this->open[$i]['F']) {
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
                if (!isset($this->fields[$j][$i])) {
                    continue;
                }
                $type = $this->fields[$j][$i];
//                if ($type == 'e') {
//                    continue;
//                }
                $terrain = Application_Model_Board::getTerrain($type, $this->canFly, $this->canSwim);
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

    public function calculateH($x, $y) {
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

    public function isCastleInterior($x, $y, $newX, $newY) {
        if ($x == $newX && $y == $newY) {
            return true;
        }
        if ($x == $newX && ($y + 1) == $newY) {
            return true;
        }
        if (($x + 1) == $newX && $y == $newY) {
            return true;
        }
        if (($x + 1) == $newX && ($y + 1) == $newY) {
            return true;
        }
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

    public function restoreFullPath($key) {
        if (!isset($this->close[$key])) {
            return null;
        }
        while (!empty($this->close[$key]['parent'])) {
            $path[] = array(
                'x' => $this->close[$key]['x'] * 40,
                'y' => $this->close[$key]['y'] * 40);
        }
        $path = array_reverse($path);
        return $path;
    }

    public function getFullPathMovesSpend($key) {
        if (!isset($this->close[$key])) {
            return null;
        }
        return $this->close[$key]['G'];
    }

    public function restorePath($key, $moves) {
        if (!isset($this->close[$key])) {
            return 0;
        }
        $this->currentPosition;
        while (!empty($this->close[$key]['parent'])) {
            if ($this->close[$key]['G'] <= $moves) {
                if (!$this->currentPosition) {
                    $this->currentPosition = array(
                        'x' => $this->close[$key]['x'] * 40,
                        'y' => $this->close[$key]['y'] * 40,
                        'movesSpend' => $this->close[$key]['G']);
                }
                $this->path[] = array(
                    'x' => $this->close[$key]['x'] * 40,
                    'y' => $this->close[$key]['y'] * 40);
            }
            $key = $this->close[$key]['parent']['x'] . '_' . $this->close[$key]['parent']['y'];
        }
        $this->path = array_reverse($this->path);
        return $this->path;
    }

    public function getCurrentPosition() {
        return $this->currentPosition;
    }

}

