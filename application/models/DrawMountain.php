<?php

class Application_Model_DrawMountain extends Application_Model_Draw
{
    private $_min = 220;
    private $_max = 255;

    protected function setInnerColors($x, $y)
    {
        $rand = rand($this->_min, $this->_max);

        $this->_colors['r'][$x][$y] = $rand;
        $this->_colors['g'][$x][$y] = $rand;
        $this->_colors['b'][$x][$y] = $rand;
    }

    protected function setBorderColors($x, $y)
    {
        $rand = rand($this->_min, $this->_max);

        $this->_colors['r'][$x][$y] = $rand;
        $this->_colors['g'][$x][$y] = $rand;
        $this->_colors['b'][$x][$y] = $rand;
    }
}