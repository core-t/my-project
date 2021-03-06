<?php

class EditorController extends Game_Controller_Gui
{
    public function indexAction()
    {
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

        $mMapFields = new Application_Model_MapFields(1);

        $mMapper = new Application_Model_Mapper($mMapFields->getMapFields());
        $mMapper->generate();
        $im = $mMapper->getIm();

        header('Content-Type: image/png');
        imagepng($im);
        imagedestroy($im);
    }
}

