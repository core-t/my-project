<?php

class Zend_View_Helper_Jquery extends Zend_View_Helper_Abstract {

    public function jquery() {
        $this->view->headScript()->prependFile('http://code.jquery.com/jquery-1.8.3.js');
    }

}