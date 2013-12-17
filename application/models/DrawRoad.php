<?php

class Application_Model_DrawRoad extends Application_Model_Draw
{
    private $_min = 117;
    private $_max = 150;

    protected $_minRadius = 0;
    protected $_maxRadius = 20;
    protected $_borderWidth = 17;
    protected $_borderHeight = 12;


    protected function setInnerColors($x, $y)
    {

        if (rand(0, 10) > 1) {
            $rand = rand($this->_min, $this->_max);

            $this->_colors['r'][$x][$y] = $rand;
            $this->_colors['g'][$x][$y] = $rand;
            $this->_colors['b'][$x][$y] = $rand;
        } else {
            $this->grass($x, $y);
        }
    }

    protected function setBorderColors($x, $y)
    {
        $this->grass($x, $y);
    }
}