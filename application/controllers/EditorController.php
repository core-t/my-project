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
        
    }

    public function indexAction()
    {
        // action body
        if ($this->_namespace->mapId) {
            unset($this->_namespace->mapId);
        }
        
    }

    public function createAction() {
        $this->view->form = new Application_Form_Createmap ();
        if ($this->_request->isPost()) {
            if ($this->view->form->isValid($this->_request->getPost())) {
                $modelMap = new Application_Model_Map ();
                $mapId = $modelMap->createMap($this->view->form->getValues(), $this->_namespace->player['playerId']);
                if($mapId){
                    $this->_helper->redirector('edit', 'editor', null, array('mapId' => $mapId));
                }
            }
        }
    }
    
    public function editAction(){
        $this->view->headLink()->appendStylesheet($this->view->baseUrl() . '/css/board.css');
        $this->view->headScript()->appendFile('/js/game/game.zoom.js');
        $this->_helper->layout->setLayout('board');
    }

}

