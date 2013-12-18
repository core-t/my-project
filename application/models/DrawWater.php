<?php

class Application_Model_DrawWater extends Application_Model_Draw
{
    protected function setInnerColors($x, $y)
    {
        $this->water($x, $y);
    }

    protected function setBorderColors($x, $y)
    {
        if (rand(0, 6) > 1) {
            $this->_colors['r'][$x][$y] = 199;
            $this->_colors['g'][$x][$y] = 199;
            $this->_colors['b'][$x][$y] = 0;
        }
    }
}