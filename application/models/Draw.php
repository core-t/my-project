<?php

class Application_Model_Draw
{
    protected $_colors = array(
        'r' => array(),
        'g' => array(),
        'b' => array()
    );
    private $_fields;
    protected $_minRadius = 13;
    protected $_maxRadius = 20;
    protected $_borderWidth = 17;
    protected $_borderHeight = 7;
    protected $_random = 3;

    public function __construct($fields)
    {
        $this->_fields = $fields;
    }

    public function getColors()
    {
        return $this->_colors;
    }

    public function setColors($colors)
    {
        $this->_colors = $colors;
    }

    protected function setOuterColors($x, $y)
    {
        $this->_colors['r'][$x][$y] = rand(40, 72);
        $this->_colors['g'][$x][$y] = rand(134, 148);
        $this->_colors['b'][$x][$y] = rand(40, 100);
    }

    function initGrass($imX1, $imX2, $imY1, $imY2)
    {
        for ($x = $imX1; $x < $imX2; $x++) {
            for ($y = $imY1; $y < $imY2; $y++) {
                $this->setOuterColors($x, $y);
            }
        }
    }

    protected function setInnerColors($x, $y)
    {
    }

    protected function setBorderColors($x, $y)
    {
    }

    function draw($fieldsY, $fieldsX, $imX1, $imY1, $imX2, $imY2, $type)
    {
        $minRadiusSquared = pow($this->_minRadius, 2);
        $maxRadiusSquared = pow($this->_maxRadius, 2);

        $centerX = $imX1 + 20;
        $centerY = $imY1 + 20;

        for ($x = $imX1; $x < $imX2; $x++) {
            for ($y = $imY1; $y < $imY2; $y++) {
                $this->setInnerColors($x, $y);

                $notSkip = 1;
                if (rand(0, $this->_random) < 1) {
                    $notSkip = 0;
                }

                $val = pow($x - $centerX, 2) + pow($y - $centerY, 2);

                if ($this->checkField($fieldsY, $fieldsX, 'left', $type)) { // brak takiego samego pola po lewej
                    if ($notSkip && $x < $imX1 + $this->_borderHeight && $y >= $imY1 + $this->_borderWidth && $y <= $imY2 - $this->_borderWidth) {
                        $this->setBorderColors($x, $y);
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'top', $type)) { // brak takiego samego pola nad
                        if ($notSkip && $x >= $imX1 + $this->_borderWidth && $x <= $imX2 - $this->_borderWidth && $y < $imY1 + $this->_borderHeight) {
                            $this->setBorderColors($x, $y);
                        }

                        if ($x <= $imX1 + $this->_borderWidth && $y <= $imY1 + $this->_borderWidth) {
                            if ($notSkip && $val < $maxRadiusSquared && $val > $minRadiusSquared) {
                                $this->setBorderColors($x, $y);
                            } elseif ($val > $maxRadiusSquared) {
                                $this->setOuterColors($x, $y);
                            }
                        }
                    } else { //jest woda nad
                        if ($notSkip && $x < $imX1 + $this->_borderHeight && $y <= $imY1 + $this->_borderWidth) {
                            $this->setBorderColors($x, $y);
                        }
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'bottom', $type)) { // brak takiego samego pola pod
                        if ($notSkip && $x >= $imX1 + $this->_borderWidth && $x <= $imX2 - $this->_borderWidth && $y > $imY2 - $this->_borderHeight) {
                            $this->setBorderColors($x, $y);
                        }

                        if ($x <= $imX1 + $this->_borderWidth && $y >= $imY2 - $this->_borderWidth) {
                            if ($notSkip && $val < $maxRadiusSquared && $val > $minRadiusSquared) {
                                $this->setBorderColors($x, $y);
                            } elseif ($val > $maxRadiusSquared) {
                                $this->setOuterColors($x, $y);
                            }
                        }
                    } else { //jest woda pod
                        if ($notSkip && $x < $imX1 + $this->_borderHeight && $y >= $imY2 - $this->_borderWidth) {
                            $this->setBorderColors($x, $y);
                        }
                    }
                } elseif ($notSkip) { // jest woda po lewej
                    if ($this->checkField($fieldsY, $fieldsX, 'top', $type)) { // brak takiego samego pola nad
                        if ($x <= $imX2 - $this->_borderWidth && $y < $imY1 + $this->_borderHeight) {
                            $this->setBorderColors($x, $y);
                        }
                    } else {
                        if ($this->checkField($fieldsY, $fieldsX, 'top-left', $type)) { // brak takiego samego pola po lewej nad
                            if ($x < $imX1 + $this->_borderHeight && $y < $imY1 + $this->_borderHeight) {
                                $this->setBorderColors($x, $y);
                            }
                        }
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'bottom', $type)) { // brak takiego samego pola pod
                        if ($x <= $imX2 - $this->_borderWidth && $y > $imY2 - $this->_borderHeight) {
                            $this->setBorderColors($x, $y);
                        }
                    } else {
                        if ($this->checkField($fieldsY, $fieldsX, 'bottom-left', $type)) { // brak takiego samego pola po lewej pod
                            if ($x < $imX1 + $this->_borderHeight && $y > $imY2 - $this->_borderHeight) {
                                $this->setBorderColors($x, $y);
                            }
                        }
                    }
                }

                if ($this->checkField($fieldsY, $fieldsX, 'right', $type)) { // brak takiego samego pola po prawej
                    if ($notSkip && $x > $imX2 - $this->_borderHeight && $y >= $imY1 + $this->_borderWidth && $y <= $imY2 - $this->_borderWidth) {
                        $this->setBorderColors($x, $y);
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'top', $type)) { // brak takiego samego pola nad
                        if ($x >= $imX2 - $this->_borderWidth && $y <= $imY1 + $this->_borderWidth) {
                            if ($notSkip && $val < $maxRadiusSquared && $val > $minRadiusSquared) {
                                $this->setBorderColors($x, $y);
                            } elseif ($val > $maxRadiusSquared) {
                                $this->setOuterColors($x, $y);
                            }
                        }
                    } else { //jest woda nad
                        if ($notSkip && $x > $imX2 - $this->_borderHeight && $y <= $imY1 + $this->_borderWidth) {
                            $this->setBorderColors($x, $y);
                        }
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'bottom', $type)) { // brak takiego samego pola pod
                        if ($x >= $imX2 - $this->_borderWidth && $y >= $imY2 - $this->_borderWidth) {
                            if ($notSkip && $val < $maxRadiusSquared && $val > $minRadiusSquared) {
                                $this->setBorderColors($x, $y);
                            } elseif ($val > $maxRadiusSquared) {
                                $this->setOuterColors($x, $y);
                            }
                        }
                    } else {
                        if ($notSkip && $x > $imX2 - $this->_borderHeight && $y >= $imY2 - $this->_borderWidth) {
                            $this->setBorderColors($x, $y);
                        }
                    }
                } elseif ($notSkip) { // jest woda po prawej
                    if ($this->checkField($fieldsY, $fieldsX, 'top', $type)) { // brak takiego samego pola nad
                        if ($x >= $imX2 - $this->_borderWidth && $y < $imY1 + $this->_borderHeight) {
                            $this->setBorderColors($x, $y);
                        }
                    } else {
                        if ($this->checkField($fieldsY, $fieldsX, 'top-right', $type)) { // brak takiego samego pola po prawej nad
                            if ($x > $imX2 - $this->_borderHeight && $y < $imY1 + $this->_borderHeight) {
                                $this->setBorderColors($x, $y);
                            }
                        }
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'bottom', $type)) { // brak takiego samego pola pod
                        if ($x >= $imX2 - $this->_borderWidth && $y > $imY2 - $this->_borderHeight) {
                            $this->setBorderColors($x, $y);
                        }
                    } else {
                        if ($this->checkField($fieldsY, $fieldsX, 'bottom-right', $type)) { // brak takiego samego pola po prawej pod
                            if ($x > $imX2 - $this->_borderHeight && $y > $imY2 - $this->_borderHeight) {
                                $this->setBorderColors($x, $y);
                            }
                        }
                    }
                }

            }
        }
    }

    protected function checkField($fieldsY, $fieldsX, $corner, $type)
    {
        switch ($corner) {
            case 'left':
                if (isset($this->_fields[$fieldsY][$fieldsX - 1])) {
                    return $this->_fields[$fieldsY][$fieldsX - 1] != $type;
                }
                break;
            case 'top':
                if (isset($this->_fields[$fieldsY - 1][$fieldsX])) {
                    return $this->_fields[$fieldsY - 1][$fieldsX] != $type;
                }
                break;
            case 'right':
                if (isset($this->_fields[$fieldsY][$fieldsX + 1])) {
                    return $this->_fields[$fieldsY][$fieldsX + 1] != $type;
                }
                break;
            case 'bottom':
                if (isset($this->_fields[$fieldsY + 1][$fieldsX])) {
                    return $this->_fields[$fieldsY + 1][$fieldsX] != $type;
                }
                break;
            case 'top-left':
                if (isset($this->_fields[$fieldsY - 1][$fieldsX - 1])) {
                    return $this->_fields[$fieldsY - 1][$fieldsX - 1] != $type;
                }
                break;
            case 'top-right':
                if (isset($this->_fields[$fieldsY - 1][$fieldsX + 1])) {
                    return $this->_fields[$fieldsY - 1][$fieldsX + 1] != $type;
                }
                break;
            case 'bottom-left':
                if (isset($this->_fields[$fieldsY + 1][$fieldsX - 1])) {
                    return $this->_fields[$fieldsY + 1][$fieldsX - 1] != $type;
                }
                break;
            case 'bottom-right':
                if (isset($this->_fields[$fieldsY + 1][$fieldsX + 1])) {
                    return $this->_fields[$fieldsY + 1][$fieldsX + 1] != $type;
                }
                break;
        }
    }

    protected function grass($x, $y)
    {
        $this->_colors['r'][$x][$y] = rand(40, 72);
        $this->_colors['g'][$x][$y] = rand(134, 148);
        $this->_colors['b'][$x][$y] = rand(40, 100);
    }

    protected function water($x, $y)
    {
        $this->_colors['r'][$x][$y] = 0;
        $this->_colors['g'][$x][$y] = rand(85, 134);
        $this->_colors['b'][$x][$y] = 199;
    }

}