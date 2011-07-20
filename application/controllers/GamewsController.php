<?php

class GamewsController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
        $this->view->headScript()->prependFile('/js/jquery.min.js');
        $this->view->headScript()->appendFile('/js/ws.js');
    }

    public function indexAction() {
        // action body
    }

}

