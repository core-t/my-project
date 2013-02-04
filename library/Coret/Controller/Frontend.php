<?php

abstract class Coret_Controller_Frontend extends Zend_Controller_Action {

    public final function init() {
        parent::init();

        // Wywołujemy funkcję _init w klasie kontrolera
        if (method_exists($this, '_init')) {
            $this->_init();
        }
        $this->_helper->layout->setLayout('core-t');

        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/core-t.css');

        $this->view->jquery();
        $this->view->headScript()->appendFile('http://jqueryrotate.googlecode.com/files/jQueryRotateCompressed.2.2.js');
        $this->view->headScript()->appendFile($this->view->baseUrl() . '/js/core-t.js');

//        $language = $this->getRequest()->getParam('lang');
        $language = Zend_Registry::get('lang');

        $this->view->headMeta()->appendHttpEquiv('Content-Language', $language);

//        new Application_View_Helper_Menu($this->getRequest()->getControllerName(), $language);
        $this->view->menu($this->getRequest()->getControllerName(), $language);
        $this->view->language($language);
        $this->view->lang($language);
        $this->view->copyright();
    }

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        parent::__construct($request, $response, $invokeArgs);
    }

}
