<?php

class Admin_OnasController extends Coret_Controller_Backend {

    public function init() {
        $this->view->title = 'O nas';
        parent::init();
        $this->params = array(
            'controller' => $this->view->controllerName
        );
    }

}

