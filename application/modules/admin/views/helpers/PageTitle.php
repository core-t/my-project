<?php

class Admin_View_Helper_PageTitle extends Zend_View_Helper_Abstract {

    public function pageTitle($text) {
        $this->view->placeholder('pagetitle')
                ->append('<div id="pageTitle"><h1><a href="/admin/' . Zend_Controller_Front::getInstance()->getRequest()->getControllerName() . '">' . $text . '</a></h1></div>');
    }

}
