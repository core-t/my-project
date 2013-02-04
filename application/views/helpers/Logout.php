<?php

class Zend_View_Helper_Logout extends Zend_View_Helper_Abstract {

    public function Logout($params) {
        $this->view->placeholder('logout')->append('<a href="/login/logout" id="logout">Logout ('.$params['firstName'].' '.$params['lastName'].')</a>');
    }

}
