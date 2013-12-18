<?php

class Application_Model_DrawMountain extends Application_Model_Draw
{
    private $_min = 220;
    private $_max = 255;

    protected $_minRadius = 0;
    protected $_maxRadius = 18;
    protected $_borderWidth = 18;
    protected $_borderHeight = 15;

    protected function setInnerColors($x, $y)
    {
        $rand = rand($this->_min, $this->_max);

        $this->_colors['r'][$x][$y] = $rand;
        $this->_colors['g'][$x][$y] = $rand;
        $this->_colors['b'][$x][$y] = $rand;
    }

    protected function setBorderColors($x, $y)
    {
        if (rand(0, 3) > 0) {
            $rand = rand(60, 110);

            $this->_colors['r'][$x][$y] = $rand;
            $this->_colors['g'][$x][$y] = $rand;
            $this->_colors['b'][$x][$y] = $rand;
        }
    }

    protected function setOuterColors($x, $y)
    {
        if (rand(0, 10) > 1) {
            $rand = rand(60, 90);

            $this->_colors['r'][$x][$y] = $rand;
            $this->_colors['g'][$x][$y] = $rand;
            $this->_colors['b'][$x][$y] = $rand;
        } else {
            $this->forest($x, $y);
        }
    }

}