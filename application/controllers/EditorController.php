<?php

class EditorController extends Game_Controller_Action
{

    public function _init()
    {
        /* Initialize action controller here */
        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css');
        $this->view->headScript()->prependFile($this->view->baseUrl() . '/js/jquery.min.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jWebSocket.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/jwsChannelPlugIn.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/index.websocket.js');
        new Application_View_Helper_Logout($this->view, $this->_namespace->player);
        new Application_View_Helper_Menu($this->view, null);
        new Application_View_Helper_Websocket($this->view, null);
        
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

    public function createAction() {
        $this->view->form = new Application_Form_Createmap ();
        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                $modelMap = new Application_Model_Map ();
                $mapId = $modelMap->createMap($this->view->form->getValues(), $this->_namespace->player['playerId']);
                if($mapId){
                    $this->_namespace->mapId = $mapId;
                    $this->_helper->redirector('edit', 'editor', null, array('mapId' => $mapId));
                }
            }
        }
    }
    
    public function editAction(){
        $mapId = $armyId = $this->_request->getParam('mapId');
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/editor.css');
        $this->view->headScript()->appendFile('/js/game/game.zoom.js');
        $this->_helper->layout->setLayout('editor');
        $modelMap = new Application_Model_Map ($mapId);
        $map = $modelMap->getMap($this->_namespace->player['playerId']);
        $map['width'] = $map['mapWidth']*40;
        $map['height'] = $map['mapHeight']*40;
//        Zend_Debug::dump($map);
        $img = imagecreatetruecolor($map['width'], $map['height']); 
        imagesavealpha($img, true); 

        // Fill the image with transparent color 
        $color = imagecolorallocatealpha($img,0x00,0x00,0x00,127);
        $color_background = imagecolorallocate($img, 255, 255, 255); 
        $color_normal = imagecolorallocate($img, 200, 200, 200); 
        $color_marked = imagecolorallocate($img, 255, 0, 0);
        imagefill($img, 0, 0, $color_background); 

        // Save the image to file.png 
        imagepng($img, APPLICATION_PATH.'/../public/img/maps/'.$map['mapId'].'.png'); 

        // Destroy image 
        imagedestroy($img); 

        new Application_View_Helper_Minimap($this->view, $map);
        new Application_View_Helper_Board($this->view, $map);
    }

}

