<?php

class Application_Model_DrawRoad extends Application_Model_Draw
{
    private $_min = 117;
    private $_max = 150;

    protected function setInnerColors($x, $y)
    {
        $rand = rand($this->_min, $this->_max);

        $this->_colors['r'][$x][$y] = $rand;
        $this->_colors['g'][$x][$y] = $rand;
        $this->_colors['b'][$x][$y] = $rand;
    }

    protected function setBorderColors($x, $y)
    {
        $this->_colors['r'][$x][$y] = rand(40, 72);
        $this->_colors['g'][$x][$y] = rand(134, 148);
        $this->_colors['b'][$x][$y] = rand(40, 100);
    }
}