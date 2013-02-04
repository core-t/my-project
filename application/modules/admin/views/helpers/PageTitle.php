<?php

class Admin_View_Helper_PageTitle extends Zend_View_Helper_Abstract {

    public function pageTitle($text) {
        $this->view->placeholder('pagetitle')
                ->append('<div id="pageTitle"><h1><a href="' . $this->view->url(array('action' => null, 'id' => null)) . '">' . $text . '</a></h1></div>');
    }

}
