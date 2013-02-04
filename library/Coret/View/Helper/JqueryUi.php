<?php

class Coret_View_Helper_JqueryUi extends Zend_View_Helper_Abstract {

    public function jqueryUi($wersja) {
        $this->view->headScript()->appendFile($this->view->baseUrl('/js/jquery-ui-' . $wersja . '.custom.min.js'));
    }

}