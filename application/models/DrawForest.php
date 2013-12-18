<?php

class Application_Model_DrawForest extends Application_Model_Draw
{
    protected $_maxRadius = 30;

    protected function setInnerColors($x, $y)
    {
        $this->forest($x, $y);
    }

    protected function setBorderColors($x, $y)
    {
        if (rand(0, 3) < 1) {
            $this->_colors['r'][$x][$y] = 0;
            $this->_colors['g'][$x][$y] = rand(64, 134);
            $this->_colors['b'][$x][$y] = rand(0, 24);
        }
    }
}