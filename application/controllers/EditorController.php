<?php

class EditorController extends Game_Controller_Gui
{

    public function _init()
    {

    }

    public function indexAction()
    {
        // action body
        if ($this->_namespace->mapId) {
            unset($this->_namespace->mapId);
        }
        $modelMap = new Application_Model_Map ();
        $this->view->mapList = $modelMap->getPlayerMapList($this->_namespace->player['playerId']);
    }

    public function createAction()
    {
        $this->view->form = new Application_Form_Createmap ();
        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                $modelMap = new Application_Model_Map ();
                $mapId = $modelMap->createMap($this->view->form->getValues(), $this->_namespace->player['playerId']);
                if ($mapId) {
                    $this->_namespace->mapId = $mapId;
                    $this->_helper->redirector('edit', 'editor', null, array('mapId' => $mapId));
                }
            }
        }
    }

    public function editAction()
    {
        $mapId = $armyId = $this->_request->getParam('mapId');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/editor.css');
        $this->view->headScript()->appendFile('/js/game/zoom.js');
        $this->_helper->layout->setLayout('editor');

        $modelMap = new Application_Model_Map($mapId);
        $map = $modelMap->getMap($this->_namespace->player['playerId']);
        $map['width'] = $map['mapWidth'] * 40;
        $map['height'] = $map['mapHeight'] * 40;

        echo $map['width'] * $map['height'] . '<br/>';

//        Zend_Debug::dump($map);
//        $img = imagecreatetruecolor($map['width'], $map['height']);
        $img = imagecreate($map['width'], $map['height']);

        if ($img) {
            imagesavealpha($img, true);

            // Fill the image with transparent color
            $color = imagecolorallocatealpha($img, 0x00, 0x00, 0x00, 127);
            $color_background = imagecolorallocate($img, 255, 255, 255);
            $color_normal = imagecolorallocate($img, 200, 200, 200);
            $color_marked = imagecolorallocate($img, 255, 0, 0);
            imagefill($img, 0, 0, $color_background);

            // Save the image to file.png
            imagepng($img, APPLICATION_PATH . '/../public/img/maps/' . $map['mapId'] . 'test.png');

            // Destroy image
            imagedestroy($img);
        }

        $this->view->map($map['mapId']);
    }

    public function testAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $numberOfFields = 15;

        $width = $numberOfFields * 40;
        $height = $width;

        $startX = 40;
        $endX = $startX + $numberOfFields;
        $startY = 80;
        $endY = $startY + $numberOfFields;

        $im = imagecreatetruecolor($width, $height);

        $colors = array(
            'r' => array(),
            'g' => array(),
            'b' => array()
        );

        $mMapFields = new Application_Model_MapFields(1);
        $fields = $mMapFields->getMapFields();

        $imY1 = 0;

        for ($fieldsY = $startY; $fieldsY < $endY; $fieldsY++) {
            $imY2 = $imY1 + 40;
            $imX1 = 0;

            for ($fieldsX = $startX; $fieldsX < $endX; $fieldsX++) {
                $type = $fields[$fieldsY][$fieldsX];

                $imX2 = $imX1 + 40;

                $colors = $this->initGrass($im, $imX1, $imX2, $imY1, $imY2, $colors);

                switch ($type) {
                    case 'g':
//                        $colors = $this->drawGrass($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2);
                        break;
                    case 'f':
//                        $colors = $this->drawForest($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2);
                        break;
                    case 'w':
                        $colors = $this->drawWater($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2);
                        break;
                    case 'M':
//                        $colors = $this->draw($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2, $type, 220, 255);
                        break;
                    case 'm':
//                        $colors = $this->draw($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2, $type, 60, 90);
                        break;
                    case 'r':
//                        $colors = $this->drawRoad($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2);
                        break;
                    case 'b':
//                        $colors = $this->drawRoad($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2);
                        break;
                }

                $imX1 += 40;
            }
            $imY1 += 40;
        }

        $this->normalize($im, $width, $colors);

        header('Content-Type: image/png');
        imagepng($im);
        imagedestroy($im);
    }

    function normalize($im, $width, $colors)
    {
        for ($y = 0; $y < $width; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $colors['r'][$x][$y] = $this->getAverageColor($colors['r'], $x, $y);
                $colors['g'][$x][$y] = $this->getAverageColor($colors['g'], $x, $y);
                $colors['b'][$x][$y] = $this->getAverageColor($colors['b'], $x, $y);

                $color = imagecolorallocate($im, $colors['r'][$x][$y], $colors['g'][$x][$y], $colors['b'][$x][$y]);
                imagefilledrectangle($im, $x, $y, $x + 1, $y + 1, $color);
            }
        }

        return $colors;
    }

    function getAverageColor($component, $x, $y)
    {
        $number = 0;
        $average = 0;

        if (!isset($component[$x][$y])) {
            return 0;
        }

        if (isset($component[$x - 1][$y - 1]) && $component[$x][$y] > $component[$x - 1][$y - 1]) {
            $number++;
            $average += $component[$x - 1][$y - 1];
        }
        if (isset($component[$x - 1][$y]) && $component[$x][$y] > $component[$x - 1][$y]) {
            $number++;
            $average += $component[$x - 1][$y];
        }
        if (isset($component[$x - 1][$y + 1]) && $component[$x][$y] > $component[$x - 1][$y + 1]) {
            $number++;
            $average += $component[$x - 1][$y + 1];
        }
        if (isset($component[$x][$y - 1]) && $component[$x][$y] > $component[$x][$y - 1]) {
            $number++;
            $average += $component[$x][$y - 1];
        }
        if (isset($component[$x][$y + 1]) && $component[$x][$y] > $component[$x][$y + 1]) {
            $number++;
            $average += $component[$x][$y + 1];
        }
        if (isset($component[$x + 1][$y - 1]) && $component[$x][$y] > $component[$x + 1][$y - 1]) {
            $number++;
            $average += $component[$x + 1][$y - 1];
        }
        if (isset($component[$x + 1][$y]) && $component[$x][$y] > $component[$x + 1][$y]) {
            $number++;
            $average += $component[$x + 1][$y];
        }
        if (isset($component[$x + 1][$y + 1]) && $component[$x][$y] > $component[$x + 1][$y + 1]) {
            $number++;
            $average += $component[$x + 1][$y + 1];
        }

        if ($number > 4) {
            return $average / $number;
        } else {
            return $component[$x][$y];
        }
    }

    function drawGrass($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2)
    {
        $colors = $this->initGrass($imX1, $imX2, $imY1, $imY2, $colors);
        return $colors;
    }

    function drawGrassCd($colors, $x, $y)
    {
        $colors['r'][$x][$y] = rand(40, 72);
        $colors['g'][$x][$y] = rand(134, 148);
        $colors['b'][$x][$y] = rand(40, 100);
        return $colors;
    }

    function initGrass($imX1, $imX2, $imY1, $imY2, $colors)
    {
        for ($x = $imX1; $x < $imX2; $x++) {
            for ($y = $imY1; $y < $imY2; $y++) {
                $colors = $this->drawGrassCd($colors, $x, $y);
            }
        }
        return $colors;
    }

    function drawForest($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2)
    {
        for ($x = $imX1; $x < $imX2; $x++) {
            for ($y = $imY1; $y < $imY2; $y++) {
                if (isset($fields[$fieldsY - 1][$fieldsX - 1]) && $fields[$fieldsY - 1][$fieldsX - 1] != 'f') {
                    if ($x < $imX1 + 10 && $y < $imY1 + 10) {
                        continue;
                    }
                }
                if (isset($fields[$fieldsY - 1][$fieldsX + 1]) && $fields[$fieldsY - 1][$fieldsX + 1] != 'f') {
                    if ($x > $imX1 + 30 && $y < $imY1 + 10) {
                        continue;
                    }
                }
                if (isset($fields[$fieldsY + 1][$fieldsX - 1]) && $fields[$fieldsY + 1][$fieldsX - 1] != 'f') {
                    if ($x < $imX1 + 10 && $y > $imY1 + 30) {
                        continue;
                    }
                }
                if (isset($fields[$fieldsY + 1][$fieldsX + 1]) && $fields[$fieldsY + 1][$fieldsX + 1] != 'f') {
                    if ($x > $imX1 + 30 && $y > $imY1 + 30) {
                        continue;
                    }
                }

                if (isset($fields[$fieldsY - 1][$fieldsX]) && $fields[$fieldsY - 1][$fieldsX] != 'f') {
                    if ($y < $imY1 + 10) {
                        continue;
                    }
                }
                if (isset($fields[$fieldsY + 1][$fieldsX]) && $fields[$fieldsY + 1][$fieldsX] != 'f') {
                    if ($y > $imY1 + 30) {
                        continue;
                    }
                }
                if (isset($fields[$fieldsY][$fieldsX - 1]) && $fields[$fieldsY][$fieldsX - 1] != 'f') {
                    if ($x < $imX1 + 10) {
                        continue;
                    }
                }
                if (isset($fields[$fieldsY][$fieldsX + 1]) && $fields[$fieldsY][$fieldsX + 1] != 'f') {
                    if ($x > $imX1 + 30) {
                        continue;
                    }
                }

                $colors['r'][$x][$y] = 0;
                $colors['g'][$x][$y] = rand(64, 134);
                $colors['b'][$x][$y] = rand(0, 24);

                $color = imagecolorallocate($im, $colors['r'][$x][$y], $colors['g'][$x][$y], $colors['b'][$x][$y]);
                imagefilledrectangle($im, $x, $y, $x + 1, $y + 1, $color);
            }
        }

        return $colors;
    }

    function draw($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2, $type, $min, $max)
    {
        for ($x = $imX1; $x < $imX2; $x++) {
            for ($y = $imY1; $y < $imY2; $y++) {
                if (isset($fields[$fieldsY - 1][$fieldsX - 1]) && $fields[$fieldsY - 1][$fieldsX - 1] != $type) {
                    if ($x < $imX1 + 10 && $y < $imY1 + 10) {
                        continue;
                    }
                }
                if (isset($fields[$fieldsY - 1][$fieldsX + 1]) && $fields[$fieldsY - 1][$fieldsX + 1] != $type) {
                    if ($x > $imX1 + 30 && $y < $imY1 + 10) {
                        continue;
                    }
                }
                if (isset($fields[$fieldsY + 1][$fieldsX - 1]) && $fields[$fieldsY + 1][$fieldsX - 1] != $type) {
                    if ($x < $imX1 + 10 && $y > $imY1 + 30) {
                        continue;
                    }
                }
                if (isset($fields[$fieldsY + 1][$fieldsX + 1]) && $fields[$fieldsY + 1][$fieldsX + 1] != $type) {
                    if ($x > $imX1 + 30 && $y > $imY1 + 30) {
                        continue;
                    }
                }

                if (isset($fields[$fieldsY - 1][$fieldsX]) && $fields[$fieldsY - 1][$fieldsX] != $type) {
                    if ($y < $imY1 + 10) {
                        continue;
                    }
                }
                if (isset($fields[$fieldsY + 1][$fieldsX]) && $fields[$fieldsY + 1][$fieldsX] != $type) {
                    if ($y > $imY1 + 30) {
                        continue;
                    }
                }
                if (isset($fields[$fieldsY][$fieldsX - 1]) && $fields[$fieldsY][$fieldsX - 1] != $type) {
                    if ($x < $imX1 + 10) {
                        continue;
                    }
                }
                if (isset($fields[$fieldsY][$fieldsX + 1]) && $fields[$fieldsY][$fieldsX + 1] != $type) {
                    if ($x > $imX1 + 30) {
                        continue;
                    }
                }

                $rand = rand($min, $max);

                $colors['r'][$x][$y] = $rand;
                $colors['g'][$x][$y] = $rand;
                $colors['b'][$x][$y] = $rand;

                $color = imagecolorallocate($im, $colors['r'][$x][$y], $colors['g'][$x][$y], $colors['b'][$x][$y]);
                imagefilledrectangle($im, $x, $y, $x + 1, $y + 1, $color);
            }
        }

        return $colors;
    }

    function drawWater($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2)
    {
        $minRadiusSquared = pow(13, 2);
        $maxRadiusSquared = pow(20, 2);
        $centerX = $imX1 + 20;
        $centerY = $imY1 + 20;
        $borderWidth = 17;
        $borderHeight = 7;

        for ($x = $imX1; $x < $imX2; $x++) {
            for ($y = $imY1; $y < $imY2; $y++) {

                $colors = $this->drawWaterCd($colors, $x, $y);

//                if (rand(0, 2) < 1) {
//                    continue;
//                }

                $val = pow($x - $centerX, 2) + pow($y - $centerY, 2);

                if ($this->checkField($fields, $fieldsY, $fieldsX, 'left', 'w')) { // brak wody po lewej
                    if ($x < $imX1 + $borderHeight && $y >= $imY1 + $borderWidth && $y <= $imY2 - $borderWidth) {
                        $colors = $this->drawSandCd($colors, $x, $y);
                    }

                    if ($this->checkField($fields, $fieldsY, $fieldsX, 'top', 'w')) { // brak wody nad
                        if ($x >= $imX1 + $borderWidth && $x <= $imX2 - $borderWidth && $y < $imY1 + $borderHeight) {
                            $colors = $this->drawSandCd($colors, $x, $y);
                        }

                        if ($x <= $imX1 + $borderWidth && $y <= $imY1 + $borderWidth) {
                            if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                                $colors = $this->drawSandCd($colors, $x, $y);
                            } elseif ($val > $maxRadiusSquared) {
                                $colors = $this->drawGrassCd($colors, $x, $y);
                            }
                        }
                    } else { //jest woda nad
                        if ($x < $imX1 + $borderHeight && $y <= $imY1 + $borderWidth) {
                            $colors = $this->drawSandCd($colors, $x, $y);
                        }
                    }

                    if ($this->checkField($fields, $fieldsY, $fieldsX, 'bottom', 'w')) { // brak wody pod
                        if ($x >= $imX1 + $borderWidth && $x <= $imX2 - $borderWidth && $y > $imY2 - $borderHeight) {
                            $colors = $this->drawSandCd($colors, $x, $y);
                        }

                        if ($x <= $imX1 + $borderWidth && $y >= $imY2 - $borderWidth) {
                            if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                                $colors = $this->drawSandCd($colors, $x, $y);
                            } elseif ($val > $maxRadiusSquared) {
                                $colors = $this->drawGrassCd($colors, $x, $y);
                            }
                        }
                    } else { //jest woda pod
                        if ($x < $imX1 + $borderHeight && $y >= $imY2 - $borderWidth) {
                            $colors = $this->drawSandCd($colors, $x, $y);
                        }
                    }
                } else { // jest woda po lewej
                    if ($this->checkField($fields, $fieldsY, $fieldsX, 'top', 'w')) { // brak wody nad
                        if ($x <= $imX2 - $borderWidth && $y < $imY1 + $borderHeight) {
                            $colors = $this->drawSandCd($colors, $x, $y);
                        }
                    } else {
                        if ($this->checkField($fields, $fieldsY, $fieldsX, 'top-left', 'w')) { // brak wody po lewej nad
                            if ($x < $imX1 + $borderHeight && $y < $imY1 + $borderHeight) {
                                $colors = $this->drawSandCd($colors, $x, $y);
                            }
                        }
                    }

                    if ($this->checkField($fields, $fieldsY, $fieldsX, 'bottom', 'w')) { // brak wody pod
                        if ($x <= $imX2 - $borderWidth && $y > $imY2 - $borderHeight) {
                            $colors = $this->drawSandCd($colors, $x, $y);
                        }
                    } else {
                        if ($this->checkField($fields, $fieldsY, $fieldsX, 'bottom-left', 'w')) { // brak wody po lewej pod
                            if ($x < $imX1 + $borderHeight && $y > $imY2 - $borderHeight) {
                                $colors = $this->drawSandCd($colors, $x, $y);
                            }
                        }
                    }
                }

                if ($this->checkField($fields, $fieldsY, $fieldsX, 'right', 'w')) { // brak wody po prawej
                    if ($x > $imX2 - $borderHeight && $y >= $imY1 + $borderWidth && $y <= $imY2 - $borderWidth) {
                        $colors = $this->drawSandCd($colors, $x, $y);
                    }

                    if ($this->checkField($fields, $fieldsY, $fieldsX, 'top', 'w')) { // brak wody nad
                        if ($x >= $imX2 - $borderWidth && $y <= $imY1 + $borderWidth) {
                            if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                                $colors = $this->drawSandCd($colors, $x, $y);
                            } elseif ($val > $maxRadiusSquared) {
                                $colors = $this->drawGrassCd($colors, $x, $y);
                            }
                        }
                    } else { //jest woda nad
                        if ($x > $imX2 - $borderHeight && $y <= $imY1 + $borderWidth) {
                            $colors = $this->drawSandCd($colors, $x, $y);
                        }
                    }

                    if ($this->checkField($fields, $fieldsY, $fieldsX, 'bottom', 'w')) { // brak wody pod
                        if ($x >= $imX2 - $borderWidth && $y >= $imY2 - $borderWidth) {
                            if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                                $colors = $this->drawSandCd($colors, $x, $y);
                            } elseif ($val > $maxRadiusSquared) {
                                $colors = $this->drawGrassCd($colors, $x, $y);
                            }
                        }
                    } else {
                        if ($x > $imX2 - $borderHeight && $y >= $imY2 - $borderWidth) {
                            $colors = $this->drawSandCd($colors, $x, $y);
                        }
                    }
                } else { // jest woda po prawej
                    if ($this->checkField($fields, $fieldsY, $fieldsX, 'top', 'w')) { // brak wody nad
                        if ($x >= $imX2 - $borderWidth && $y < $imY1 + $borderHeight) {
                            $colors = $this->drawSandCd($colors, $x, $y);
                        }
                    } else {
                        if ($this->checkField($fields, $fieldsY, $fieldsX, 'top-right', 'w')) { // brak wody po prawej nad
                            if ($x > $imX2 - $borderHeight && $y < $imY1 + $borderHeight) {
                                $colors = $this->drawSandCd($colors, $x, $y);
                            }
                        }
                    }

                    if ($this->checkField($fields, $fieldsY, $fieldsX, 'bottom', 'w')) { // brak wody pod
                        if ($x >= $imX2 - $borderWidth && $y > $imY2 - $borderHeight) {
                            $colors = $this->drawSandCd($colors, $x, $y);
                        }
                    } else {
                        if ($this->checkField($fields, $fieldsY, $fieldsX, 'bottom-right', 'w')) { // brak wody po prawej pod
                            if ($x > $imX2 - $borderHeight && $y > $imY2 - $borderHeight) {
                                $colors = $this->drawSandCd($colors, $x, $y);
                            }
                        }
                    }
                }

            }
        }

        return $colors;
    }

    function drawWaterCd($colors, $x, $y)
    {
        $colors['r'][$x][$y] = 0;
        $colors['g'][$x][$y] = rand(85, 134);
        $colors['b'][$x][$y] = 199;
        return $colors;
    }

    function drawSandCd($colors, $x, $y)
    {
        $colors['r'][$x][$y] = 199;
        $colors['g'][$x][$y] = 199;
        $colors['b'][$x][$y] = 0;
        return $colors;
    }

    function checkField($fields, $fieldsY, $fieldsX, $corner, $type)
    {
        switch ($corner) {
            case 'left':
                if (isset($fields[$fieldsY][$fieldsX - 1])) {
                    return $fields[$fieldsY][$fieldsX - 1] != $type;
                }
                break;
            case 'top':
                if (isset($fields[$fieldsY - 1][$fieldsX])) {
                    return $fields[$fieldsY - 1][$fieldsX] != $type;
                }
                break;
            case 'right':
                if (isset($fields[$fieldsY][$fieldsX + 1])) {
                    return $fields[$fieldsY][$fieldsX + 1] != $type;
                }
                break;
            case 'bottom':
                if (isset($fields[$fieldsY + 1][$fieldsX])) {
                    return $fields[$fieldsY + 1][$fieldsX] != $type;
                }
                break;
            case 'top-left':
                if (isset($fields[$fieldsY - 1][$fieldsX - 1])) {
                    return $fields[$fieldsY - 1][$fieldsX - 1] != $type;
                }
                break;
            case 'top-right':
                if (isset($fields[$fieldsY - 1][$fieldsX + 1])) {
                    return $fields[$fieldsY - 1][$fieldsX + 1] != $type;
                }
                break;
            case 'bottom-left':
                if (isset($fields[$fieldsY + 1][$fieldsX - 1])) {
                    return $fields[$fieldsY + 1][$fieldsX - 1] != $type;
                }
                break;
            case 'bottom-right':
                if (isset($fields[$fieldsY + 1][$fieldsX + 1])) {
                    return $fields[$fieldsY + 1][$fieldsX + 1] != $type;
                }
                break;
        }
    }

    function drawWater1($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2)
    {
        $yellow = imagecolorallocate($im, 199, 199, 0);
        $minRadiusSquared = pow(15, 2);
        $maxRadiusSquared = pow(20, 2);
        $centerX = $imX1 + 20;
        $centerY = $imY1 + 20;

        $border = 10;

        for ($x = $imX1; $x < $imX2; $x++) {
            for ($y = $imY1; $y < $imY2; $y++) {

                $val = pow($x - $centerX, 2) + pow($y - $centerY, 2);
                if ($val < $minRadiusSquared) {
                    $colors['r'][$x][$y] = 0;
                    $colors['g'][$x][$y] = rand(85, 134);
                    $colors['b'][$x][$y] = 199;

                    $color = imagecolorallocate($im, $colors['r'][$x][$y], $colors['g'][$x][$y], $colors['b'][$x][$y]);
                    imagefilledrectangle($im, $x, $y, $x + 1, $y + 1, $color);
                }


                if (isset($fields[$fieldsY - 1][$fieldsX - 1]) && $fields[$fieldsY - 1][$fieldsX - 1] != 'w') {
                    if ($x < $imX1 + $border && $y < $imY1 + $border) {
                        if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                            $colors['r'][$x][$y] = 199;
                            $colors['g'][$x][$y] = 199;
                            $colors['b'][$x][$y] = 0;
                            imagefilledrectangle($im, $x, $y, $x + 1, $y + 1, $yellow);
                        }
                        continue;
                    }
                }
                if (isset($fields[$fieldsY - 1][$fieldsX + 1]) && $fields[$fieldsY - 1][$fieldsX + 1] != 'w') {
                    if ($x > $imX2 - $border && $y < $imY1 + $border) {
                        if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                            $colors['r'][$x][$y] = 199;
                            $colors['g'][$x][$y] = 199;
                            $colors['b'][$x][$y] = 0;
                            imagefilledrectangle($im, $x, $y, $x + 1, $y + 1, $yellow);
                        }
                        continue;
                    }
                }
                if (isset($fields[$fieldsY + 1][$fieldsX - 1]) && $fields[$fieldsY + 1][$fieldsX - 1] != 'w') {
                    if ($x < $imX1 + $border && $y > $imY2 - $border) {
                        if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                            $colors['r'][$x][$y] = 199;
                            $colors['g'][$x][$y] = 199;
                            $colors['b'][$x][$y] = 0;
                            imagefilledrectangle($im, $x, $y, $x + 1, $y + 1, $yellow);
                        }
                        continue;
                    }
                }
                if (isset($fields[$fieldsY + 1][$fieldsX + 1]) && $fields[$fieldsY + 1][$fieldsX + 1] != 'w') {
                    if ($x > $imX2 - $border && $y > $imY2 - $border) {
                        if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                            $colors['r'][$x][$y] = 199;
                            $colors['g'][$x][$y] = 199;
                            $colors['b'][$x][$y] = 0;
                            imagefilledrectangle($im, $x, $y, $x + 1, $y + 1, $yellow);
                        }
                        continue;
                    }
                }

                if (isset($fields[$fieldsY - 1][$fieldsX]) && $fields[$fieldsY - 1][$fieldsX] != 'w') {
                    if ($y < $imY1 + $border) {
                        if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                            $colors['r'][$x][$y] = 199;
                            $colors['g'][$x][$y] = 199;
                            $colors['b'][$x][$y] = 0;
                            imagefilledrectangle($im, $x, $y, $x + 1, $y + 1, $yellow);
                        }
                        continue;
                    }
                }
                if (isset($fields[$fieldsY + 1][$fieldsX]) && $fields[$fieldsY + 1][$fieldsX] != 'w') {
                    if ($y > $imY2 - $border) {
                        if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                            $colors['r'][$x][$y] = 199;
                            $colors['g'][$x][$y] = 199;
                            $colors['b'][$x][$y] = 0;
                            imagefilledrectangle($im, $x, $y, $x + 1, $y + 1, $yellow);
                        }
                        continue;
                    }
                }
                if (isset($fields[$fieldsY][$fieldsX - 1]) && $fields[$fieldsY][$fieldsX - 1] != 'w') {
                    if ($x < $imX1 + $border) {
                        if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                            $colors['r'][$x][$y] = 199;
                            $colors['g'][$x][$y] = 199;
                            $colors['b'][$x][$y] = 0;
                            imagefilledrectangle($im, $x, $y, $x + 1, $y + 1, $yellow);
                        }
                        continue;
                    }
                }
                if (isset($fields[$fieldsY][$fieldsX + 1]) && $fields[$fieldsY][$fieldsX + 1] != 'w') {
                    if ($x > $imX2 - $border) {
                        if ($val < $maxRadiusSquared && $val > $minRadiusSquared) {
                            $colors['r'][$x][$y] = 199;
                            $colors['g'][$x][$y] = 199;
                            $colors['b'][$x][$y] = 0;
                            imagefilledrectangle($im, $x, $y, $x + 1, $y + 1, $yellow);
                        }
                        continue;
                    }
                }
            }
        }

        return $colors;
    }

    function drawRoad($fields, $fieldsY, $fieldsX, $colors, $im, $imX1, $imY1, $imX2, $imY2)
    {
        for ($x = $imX1; $x < $imX2; $x++) {
            for ($y = $imY1; $y < $imY2; $y++) {
                if ($x < $imX1 + 10 && $y < $imY1 + 10) {
                    continue;
                }
                if ($x > $imX1 + 30 && $y < $imY1 + 10) {
                    continue;
                }
                if ($x < $imX1 + 10 && $y > $imY1 + 30) {
                    continue;
                }
                if ($x > $imX1 + 30 && $y > $imY1 + 30) {
                    continue;
                }
                if (isset($fields[$fieldsY - 1][$fieldsX]) && $fields[$fieldsY - 1][$fieldsX] != 'r' && $fields[$fieldsY - 1][$fieldsX] != 'b') {
                    if ($y < $imY1 + 10) {
                        continue;
                    }
                }
                if (isset($fields[$fieldsY + 1][$fieldsX]) && $fields[$fieldsY + 1][$fieldsX] != 'r' && $fields[$fieldsY + 1][$fieldsX] != 'b') {
                    if ($y > $imY1 + 30) {
                        continue;
                    }
                }
                if (isset($fields[$fieldsY][$fieldsX - 1]) && $fields[$fieldsY][$fieldsX - 1] != 'r' && $fields[$fieldsY][$fieldsX - 1] != 'b') {
                    if ($x < $imX1 + 10) {
                        continue;
                    }
                }
                if (isset($fields[$fieldsY][$fieldsX + 1]) && $fields[$fieldsY][$fieldsX + 1] != 'r' && $fields[$fieldsY][$fieldsX + 1] != 'b') {
                    if ($x > $imX1 + 30) {
                        continue;
                    }
                }
                $colors['r'][$x][$y] = rand(117, 150);
                $colors['g'][$x][$y] = rand(117, 150);
                $colors['b'][$x][$y] = rand(117, 150);

                $color = imagecolorallocate($im, $colors['r'][$x][$y], $colors['g'][$x][$y], $colors['b'][$x][$y]);
                imagefilledrectangle($im, $x, $y, $x + 1, $y + 1, $color);
            }
        }

        return $colors;
    }

}

