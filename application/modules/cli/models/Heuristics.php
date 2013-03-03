<?php

class Cli_Model_Heuristics {

    /**
     * Destination x value
     *
     * @var int
     */
    protected $destX;

    /**
     * Destination y value
     *
     * @var int
     */
    protected $destY;

    public function __construct($destX, $destY) {
        $this->destX = $destX;
        $this->destY = $destY;
    }

    /**
     * Calculates heuristic estimate
     *
     * @param int $x
     * @param int $y
     * @return int
     */
    public function calculateH($x, $y) {
//        $h = 0;
//        $xLengthPoints = abs($x - $this->destX);
//        $yLengthPoints = abs($y - $this->destY);
//        if ($xLengthPoints < $yLengthPoints) {
//            for ($i = 1; $i <= $xLengthPoints; $i++)
//            {
//                $h++;
//            }
//            for ($i = 1; $i <= ($yLengthPoints - $xLengthPoints); $i++)
//            {
//                $h++;
//            }
//        } else {
//            for ($i = 1; $i <= $yLengthPoints; $i++)
//            {
//                $h++;
//            }
//            for ($i = 1; $i <= ($xLengthPoints - $yLengthPoints); $i++)
//            {
//                $h++;
//            }
//        }
//        return $h;
        return sqrt(pow($this->destX - $x, 2) + pow($y - $this->destY, 2));
    }

}