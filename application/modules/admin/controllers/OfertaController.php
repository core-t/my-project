<?php

class Admin_OfertaController extends Coret_Controller_Backend {

    public $params = null;

    public function init() {
        $this->view->title = 'Oferta';
        parent::init();
        $this->params = array(
            'controller' => $this->view->controllerName
        );
    }

}

