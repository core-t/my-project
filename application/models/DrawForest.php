<?php

class Application_Model_DrawForest extends Application_Model_Draw
{
    protected function setInnerColors($x, $y)
    {
        $this->_colors['r'][$x][$y] = 0;
        $this->_colors['g'][$x][$y] = rand(64, 134);
        $this->_colors['b'][$x][$y] = rand(0, 24);
    }

    protected function setBorderColors($x, $y)
    {
    }
}