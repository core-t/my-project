<?php

class Zend_View_Helper_Copyright extends Zend_View_Helper_Abstract {

    public function copyright() {
        $this->view->placeholder('copyright')->append($this->view->translate('All rights reserved') . '. Copyright <a href="http://core-t.pl" title="core-t.pl">core-t.pl</a> &copy;2012.');
    }

}

