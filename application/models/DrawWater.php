<?php

class Application_Model_DrawWater extends Application_Model_Draw
{
    protected function setInnerColors($x, $y)
    {
        $this->_colors['r'][$x][$y] = 0;
        $this->_colors['g'][$x][$y] = rand(85, 134);
        $this->_colors['b'][$x][$y] = 199;
    }

    protected function setBorderColors($x, $y)
    {
        $this->_colors['r'][$x][$y] = 199;
        $this->_colors['g'][$x][$y] = 199;
        $this->_colors['b'][$x][$y] = 0;
    }
}