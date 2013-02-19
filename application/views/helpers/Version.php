<?php

class Zend_View_Helper_Version extends Zend_View_Helper_Abstract {

    public function Version() {
        $this->view->placeholder('version')->append('<div class="left font-orange" id="versionNumber">' . $this->view->translate('Version') . ': <span>' . Zend_Registry::get('config')->version . '</span></div>');
    }

}
