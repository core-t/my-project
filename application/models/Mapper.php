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

    private $_numberOfFields = 15;

    public function __construct($fields)
    {
        $this->_fields = $fields;

        $this->_width = $this->_numberOfFields * 40;
        $this->_height = $this->_width;

        $this->_startX = 39;
        $this->_endX = $this->_startX + $this->_numberOfFields;
        $this->_startY = 80;
        $this->_endY = $this->_startY + $this->_numberOfFields;

        $this->_im = imagecreatetruecolor($this->_width, $this->_height);
    }

    public function getIm()
    {
        return $this->_im;
    }

    public function generate()
    {
        $mDraw = new Application_Model_Draw($this->_fields);
        $mDrawForest = new Application_Model_DrawForest($this->_fields);
        $mDrawWater = new Application_Model_DrawWater($this->_fields);
        $mDrawMountain = new Application_Model_DrawMountain($this->_fields);
        $mDrawHill = new Application_Model_DrawHill($this->_fields);
        $mDrawRoad = new Application_Model_DrawRoad($this->_fields);
        $mDrawBridge = new Application_Model_DrawBridge($this->_fields);
        $imY1 = 0;

        for ($fieldsY = $this->_startY; $fieldsY < $this->_endY; $fieldsY++) {
            $imY2 = $imY1 + 40;
            $imX1 = 0;

            for ($fieldsX = $this->_startX; $fieldsX < $this->_endX; $fieldsX++) {
                $type = $this->_fields[$fieldsY][$fieldsX];

                $imX2 = $imX1 + 40;

                $mDraw->setColors($this->_colors);
                $mDraw->initGrass($imX1, $imX2, $imY1, $imY2);
                $this->_colors = $mDraw->getColors();

                switch ($type) {
                    case 'g':
                        break;
                    case 'f':
                        $mDrawForest->setColors($this->_colors);
                        $mDrawForest->draw($fieldsY, $fieldsX, $imX1, $imY1, $imX2, $imY2, $type);
                        $this->_colors = $mDrawForest->getColors();
                        break;
                    case 'w':
                        $mDrawWater->setColors($this->_colors);
                        $mDrawWater->draw($fieldsY, $fieldsX, $imX1, $imY1, $imX2, $imY2, $type);
                        $this->_colors = $mDrawWater->getColors();
                        break;
                    case 'M':
                        $mDrawMountain->setColors($this->_colors);
                        $mDrawMountain->draw($fieldsY, $fieldsX, $imX1, $imY1, $imX2, $imY2, $type);
                        $this->_colors = $mDrawMountain->getColors();
                        break;
                    case 'm':
                        $mDrawHill->setColors($this->_colors);
                        $mDrawHill->draw($fieldsY, $fieldsX, $imX1, $imY1, $imX2, $imY2, $type);
                        $this->_colors = $mDrawHill->getColors();
                        break;
                    case 'r':
                        $mDrawRoad->setColors($this->_colors);
                        $mDrawRoad->draw($fieldsY, $fieldsX, $imX1, $imY1, $imX2, $imY2, $type);
                        $this->_colors = $mDrawRoad->getColors();
                        break;
                    case 'b':
                        $mDrawBridge->setColors($this->_colors);
                        $mDrawBridge->draw($fieldsY, $fieldsX, $imX1, $imY1, $imX2, $imY2, $type);
                        $this->_colors = $mDrawBridge->getColors();
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

}