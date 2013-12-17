<?php

class Application_Model_Draw
{
    protected $_colors = array(
        'r' => array(),
        'g' => array(),
        'b' => array()
    );
    private $_fields;

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
        $minRadiusSquared = pow(13, 2);
        $maxRadiusSquared = pow(20, 2);
        $centerX = $imX1 + 20;
        $centerY = $imY1 + 20;
        $borderWidth = 17;
        $borderHeight = 7;

        for ($x = $imX1; $x < $imX2; $x++) {
            for ($y = $imY1; $y < $imY2; $y++) {

                $this->setInnerColors($x, $y);

                if (rand(0, 2) < 1) {
                    continue;
                }

                $val = pow($x - $centerX, 2) + pow($y - $centerY, 2);

                if ($this->checkField($fieldsY, $fieldsX, 'left', $type)) { // brak takiego samego pola po lewej
                    if ($x < $imX1 + $borderHeight && $y >= $imY1 + $borderWidth && $y <= $imY2 - $borderWidth) {
                        $this->setBorderColors($x, $y);
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'top', $type)) { // brak takiego samego pola nad
                        if ($x >= $imX1 + $borderWidth && $x <= $imX2 - $borderWidth && $y < $imY1 + $borderHeight) {
                            $this->setBorderColors($x, $y);
                        }

                        if ($x <= $imX1 + $borderWidth && $y <= $imY1 + $borderWidth) {
                            if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                                $this->setBorderColors($x, $y);
                            } elseif ($val > $maxRadiusSquared) {
                                $this->setOuterColors($x, $y);
                            }
                        }
                    } else { //jest woda nad
                        if ($x < $imX1 + $borderHeight && $y <= $imY1 + $borderWidth) {
                            $this->setBorderColors($x, $y);
                        }
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'bottom', $type)) { // brak takiego samego pola pod
                        if ($x >= $imX1 + $borderWidth && $x <= $imX2 - $borderWidth && $y > $imY2 - $borderHeight) {
                            $this->setBorderColors($x, $y);
                        }

                        if ($x <= $imX1 + $borderWidth && $y >= $imY2 - $borderWidth) {
                            if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                                $this->setBorderColors($x, $y);
                            } elseif ($val > $maxRadiusSquared) {
                                $this->setOuterColors($x, $y);
                            }
                        }
                    } else { //jest woda pod
                        if ($x < $imX1 + $borderHeight && $y >= $imY2 - $borderWidth) {
                            $this->setBorderColors($x, $y);
                        }
                    }
                } else { // jest woda po lewej
                    if ($this->checkField($fieldsY, $fieldsX, 'top', $type)) { // brak takiego samego pola nad
                        if ($x <= $imX2 - $borderWidth && $y < $imY1 + $borderHeight) {
                            $this->setBorderColors($x, $y);
                        }
                    } else {
                        if ($this->checkField($fieldsY, $fieldsX, 'top-left', $type)) { // brak takiego samego pola po lewej nad
                            if ($x < $imX1 + $borderHeight && $y < $imY1 + $borderHeight) {
                                $this->setBorderColors($x, $y);
                            }
                        }
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'bottom', $type)) { // brak takiego samego pola pod
                        if ($x <= $imX2 - $borderWidth && $y > $imY2 - $borderHeight) {
                            $this->setBorderColors($x, $y);
                        }
                    } else {
                        if ($this->checkField($fieldsY, $fieldsX, 'bottom-left', $type)) { // brak takiego samego pola po lewej pod
                            if ($x < $imX1 + $borderHeight && $y > $imY2 - $borderHeight) {
                                $this->setBorderColors($x, $y);
                            }
                        }
                    }
                }

                if ($this->checkField($fieldsY, $fieldsX, 'right', $type)) { // brak takiego samego pola po prawej
                    if ($x > $imX2 - $borderHeight && $y >= $imY1 + $borderWidth && $y <= $imY2 - $borderWidth) {
                        $this->setBorderColors($x, $y);
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'top', $type)) { // brak takiego samego pola nad
                        if ($x >= $imX2 - $borderWidth && $y <= $imY1 + $borderWidth) {
                            if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                                $this->setBorderColors($x, $y);
                            } elseif ($val > $maxRadiusSquared) {
                                $this->setOuterColors($x, $y);
                            }
                        }
                    } else { //jest woda nad
                        if ($x > $imX2 - $borderHeight && $y <= $imY1 + $borderWidth) {
                            $this->setBorderColors($x, $y);
                        }
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'bottom', $type)) { // brak takiego samego pola pod
                        if ($x >= $imX2 - $borderWidth && $y >= $imY2 - $borderWidth) {
                            if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                                $this->setBorderColors($x, $y);
                            } elseif ($val > $maxRadiusSquared) {
                                $this->setOuterColors($x, $y);
                            }
                        }
                    } else {
                        if ($x > $imX2 - $borderHeight && $y >= $imY2 - $borderWidth) {
                            $this->setBorderColors($x, $y);
                        }
                    }
                } else { // jest woda po prawej
                    if ($this->checkField($fieldsY, $fieldsX, 'top', $type)) { // brak takiego samego pola nad
                        if ($x >= $imX2 - $borderWidth && $y < $imY1 + $borderHeight) {
                            $this->setBorderColors($x, $y);
                        }
                    } else {
                        if ($this->checkField($fieldsY, $fieldsX, 'top-right', $type)) { // brak takiego samego pola po prawej nad
                            if ($x > $imX2 - $borderHeight && $y < $imY1 + $borderHeight) {
                                $this->setBorderColors($x, $y);
                            }
                        }
                    }

                    if ($this->checkField($fieldsY, $fieldsX, 'bottom', $type)) { // brak takiego samego pola pod
                        if ($x >= $imX2 - $borderWidth && $y > $imY2 - $borderHeight) {
                            $this->setBorderColors($x, $y);
                        }
                    } else {
                        if ($this->checkField($fieldsY, $fieldsX, 'bottom-right', $type)) { // brak takiego samego pola po prawej pod
                            if ($x > $imX2 - $borderHeight && $y > $imY2 - $borderHeight) {
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

}