<?php

class Application_Model_Mapper
{
    private $_im;
    private $_colors = array(
        'r' => array(),
        'g' => array(),
        'b' => array()
    );
    private $_fields;
    private $_width;
    private $_height;

    private $_startX;
    private $_endX;
    private $_startY;
    private $_endY;

    static private $_numberOfFields = 15;


    public function __construct($fields)
    {
        $this->_fields = $fields;

        $this->_width = $this->_numberOfFields * 40;
        $this->_height = $this->_width;

        $this->_startX = 40;
        $this->_endX = $this->_startX + $this->numberOfFields;
        $this->_startY = 80;
        $this->_endY = $this->_startY + $this->numberOfFields;

        $this->_im = imagecreatetruecolor($this->_width, $this->_height);
    }

    public function generate()
    {
        $imY1 = 0;

        for ($fieldsY = $this->_startY; $fieldsY < $this->_endY; $fieldsY++) {
            $imY2 = $imY1 + 40;
            $imX1 = 0;

            for ($fieldsX = $this->_startX; $fieldsX < $this->_endX; $fieldsX++) {
                $type = $this->_fields[$fieldsY][$fieldsX];

                $imX2 = $imX1 + 40;

                $this->initGrass($imX1, $imX2, $imY1, $imY2);

                switch ($type) {
                    case 'g':
//                        $colors = $this->drawGrass($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2);
                        break;
                    case 'f':
//                        $colors = $this->drawForest($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2);
                        break;
                    case 'w':
                        $colors = $this->drawWater($fieldsY, $fieldsX, $imX1, $imY1, $imX2, $imY2);
                        break;
                    case 'M':
//                        $colors = $this->draw($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2, $type, 220, 255);
                        break;
                    case 'm':
//                        $colors = $this->draw($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2, $type, 60, 90);
                        break;
                    case 'r':
//                        $colors = $this->drawRoad($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2);
                        break;
                    case 'b':
//                        $colors = $this->drawRoad($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2);
                        break;
                }

                $imX1 += 40;
            }
            $imY1 += 40;
        }

        $this->normalize();
    }

    private function normalize()
    {
        for ($y = 0; $y < $this->_height; $y++) {
            for ($x = 0; $x < $this->_width; $x++) {
                $this->_colors['r'][$x][$y] = $this->getAverageColor($this->_colors['r'], $x, $y);
                $this->_colors['g'][$x][$y] = $this->getAverageColor($this->_colors['g'], $x, $y);
                $this->_colors['b'][$x][$y] = $this->getAverageColor($this->_colors['b'], $x, $y);

                $color = imagecolorallocate($this->_im, $this->_colors['r'][$x][$y], $this->_colors['g'][$x][$y], $this->_colors['b'][$x][$y]);
                imagefilledrectangle($this->_im, $x, $y, $x + 1, $y + 1, $color);
            }
        }
    }

    private function getAverageColor($component, $x, $y)
    {
        $number = 0;
        $average = 0;

        if (!isset($component[$x][$y])) {
            return 0;
        }

        if (isset($component[$x - 1][$y - 1]) && $component[$x][$y] > $component[$x - 1][$y - 1]) {
            $number++;
            $average += $component[$x - 1][$y - 1];
        }
        if (isset($component[$x - 1][$y]) && $component[$x][$y] > $component[$x - 1][$y]) {
            $number++;
            $average += $component[$x - 1][$y];
        }
        if (isset($component[$x - 1][$y + 1]) && $component[$x][$y] > $component[$x - 1][$y + 1]) {
            $number++;
            $average += $component[$x - 1][$y + 1];
        }
        if (isset($component[$x][$y - 1]) && $component[$x][$y] > $component[$x][$y - 1]) {
            $number++;
            $average += $component[$x][$y - 1];
        }
        if (isset($component[$x][$y + 1]) && $component[$x][$y] > $component[$x][$y + 1]) {
            $number++;
            $average += $component[$x][$y + 1];
        }
        if (isset($component[$x + 1][$y - 1]) && $component[$x][$y] > $component[$x + 1][$y - 1]) {
            $number++;
            $average += $component[$x + 1][$y - 1];
        }
        if (isset($component[$x + 1][$y]) && $component[$x][$y] > $component[$x + 1][$y]) {
            $number++;
            $average += $component[$x + 1][$y];
        }
        if (isset($component[$x + 1][$y + 1]) && $component[$x][$y] > $component[$x + 1][$y + 1]) {
            $number++;
            $average += $component[$x + 1][$y + 1];
        }

        if ($number > 4) {
            return $average / $number;
        } else {
            return $component[$x][$y];
        }
    }

    function drawGrassCd($x, $y)
    {
        $this->_colors['r'][$x][$y] = rand(40, 72);
        $this->_colors['g'][$x][$y] = rand(134, 148);
        $this->_colors['b'][$x][$y] = rand(40, 100);
    }

    function initGrass($imX1, $imX2, $imY1, $imY2)
    {
        for ($x = $imX1; $x < $imX2; $x++) {
            for ($y = $imY1; $y < $imY2; $y++) {
                $this->drawGrassCd($x, $y);
            }
        }
    }

    function drawWater($fieldsY, $fieldsX, $imX1, $imY1, $imX2, $imY2)
    {
        $minRadiusSquared = pow(13, 2);
        $maxRadiusSquared = pow(20, 2);
        $centerX = $imX1 + 20;
        $centerY = $imY1 + 20;
        $borderWidth = 17;
        $borderHeight = 7;

        for ($x = $imX1; $x < $imX2; $x++) {
            for ($y = $imY1; $y < $imY2; $y++) {

                $this->drawWaterCd($x, $y);

//                if (rand(0, 2) < 1) {
//                    continue;
//                }

                $val = pow($x - $centerX, 2) + pow($y - $centerY, 2);

                if ($this->checkField($fieldsY, $fieldsX, 'left', 'w')) { // brak wody po lewej
                    if ($x < $imX1 + $borderHeight && $y >= $imY1 + $borderWidth && $y <= $imY2 - $borderWidth) {
                        $this->drawSandCd($x, $y);
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'top', 'w')) { // brak wody nad
                        if ($x >= $imX1 + $borderWidth && $x <= $imX2 - $borderWidth && $y < $imY1 + $borderHeight) {
                            $this->drawSandCd($x, $y);
                        }

                        if ($x <= $imX1 + $borderWidth && $y <= $imY1 + $borderWidth) {
                            if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                                $this->drawSandCd($x, $y);
                            } elseif ($val > $maxRadiusSquared) {
                                $this->drawGrassCd($x, $y);
                            }
                        }
                    } else { //jest woda nad
                        if ($x < $imX1 + $borderHeight && $y <= $imY1 + $borderWidth) {
                            $this->drawSandCd($x, $y);
                        }
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'bottom', 'w')) { // brak wody pod
                        if ($x >= $imX1 + $borderWidth && $x <= $imX2 - $borderWidth && $y > $imY2 - $borderHeight) {
                            $this->drawSandCd($x, $y);
                        }

                        if ($x <= $imX1 + $borderWidth && $y >= $imY2 - $borderWidth) {
                            if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                                $this->drawSandCd($x, $y);
                            } elseif ($val > $maxRadiusSquared) {
                                $this->drawGrassCd($x, $y);
                            }
                        }
                    } else { //jest woda pod
                        if ($x < $imX1 + $borderHeight && $y >= $imY2 - $borderWidth) {
                            $this->drawSandCd($x, $y);
                        }
                    }
                } else { // jest woda po lewej
                    if ($this->checkField($fieldsY, $fieldsX, 'top', 'w')) { // brak wody nad
                        if ($x <= $imX2 - $borderWidth && $y < $imY1 + $borderHeight) {
                            $this->drawSandCd($x, $y);
                        }
                    } else {
                        if ($this->checkField($fieldsY, $fieldsX, 'top-left', 'w')) { // brak wody po lewej nad
                            if ($x < $imX1 + $borderHeight && $y < $imY1 + $borderHeight) {
                                $this->drawSandCd($x, $y);
                            }
                        }
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'bottom', 'w')) { // brak wody pod
                        if ($x <= $imX2 - $borderWidth && $y > $imY2 - $borderHeight) {
                            $this->drawSandCd($x, $y);
                        }
                    } else {
                        if ($this->checkField($fieldsY, $fieldsX, 'bottom-left', 'w')) { // brak wody po lewej pod
                            if ($x < $imX1 + $borderHeight && $y > $imY2 - $borderHeight) {
                                $this->drawSandCd($x, $y);
                            }
                        }
                    }
                }

                if ($this->checkField($fieldsY, $fieldsX, 'right', 'w')) { // brak wody po prawej
                    if ($x > $imX2 - $borderHeight && $y >= $imY1 + $borderWidth && $y <= $imY2 - $borderWidth) {
                        $this->drawSandCd($x, $y);
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'top', 'w')) { // brak wody nad
                        if ($x >= $imX2 - $borderWidth && $y <= $imY1 + $borderWidth) {
                            if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                                $this->drawSandCd($x, $y);
                            } elseif ($val > $maxRadiusSquared) {
                                $this->drawGrassCd($x, $y);
                            }
                        }
                    } else { //jest woda nad
                        if ($x > $imX2 - $borderHeight && $y <= $imY1 + $borderWidth) {
                            $this->drawSandCd($x, $y);
                        }
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'bottom', 'w')) { // brak wody pod
                        if ($x >= $imX2 - $borderWidth && $y >= $imY2 - $borderWidth) {
                            if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                                $this->drawSandCd($x, $y);
                            } elseif ($val > $maxRadiusSquared) {
                                $this->drawGrassCd($x, $y);
                            }
                        }
                    } else {
                        if ($x > $imX2 - $borderHeight && $y >= $imY2 - $borderWidth) {
                            $this->drawSandCd($x, $y);
                        }
                    }
                } else { // jest woda po prawej
                    if ($this->checkField($fieldsY, $fieldsX, 'top', 'w')) { // brak wody nad
                        if ($x >= $imX2 - $borderWidth && $y < $imY1 + $borderHeight) {
                            $this->drawSandCd($x, $y);
                        }
                    } else {
                        if ($this->checkField($fieldsY, $fieldsX, 'top-right', 'w')) { // brak wody po prawej nad
                            if ($x > $imX2 - $borderHeight && $y < $imY1 + $borderHeight) {
                                $this->drawSandCd($x, $y);
                            }
                        }
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'bottom', 'w')) { // brak wody pod
                        if ($x >= $imX2 - $borderWidth && $y > $imY2 - $borderHeight) {
                            $this->drawSandCd($x, $y);
                        }
                    } else {
                        if ($this->checkField($fieldsY, $fieldsX, 'bottom-right', 'w')) { // brak wody po prawej pod
                            if ($x > $imX2 - $borderHeight && $y > $imY2 - $borderHeight) {
                                $this->drawSandCd($x, $y);
                            }
                        }
                    }
                }

            }
        }
    }
}