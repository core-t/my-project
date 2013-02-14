<?php

class Zend_View_Helper_Version extends Zend_View_Helper_Abstract {

    public function Version() {
        $this->view->placeholder('version')->append('<div class="left" id="versionNumber">Version number: <span class="font-yellow">' . Zend_Registry::get('config')->version . '</span></div>');
    }

}
