<?php

class Admin_KontaktController extends Coret_Controller_Backend {

    public function init() {
        $this->view->title = 'Kontakt';
        parent::init();
        $this->params = array(
            'controller' => $this->view->controllerName
        );
    }

    public function indexAction() {

    }

}

