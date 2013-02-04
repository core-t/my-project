<?php

class Coret_View_Helper_JqueryUpload extends Zend_View_Helper_Abstract {

    public function jqueryUpload($version) {
        $this->view->headScript()->appendFile($this->view->baseUrl('/js/jquery.upload-' . $version . '.js'));
    }

}