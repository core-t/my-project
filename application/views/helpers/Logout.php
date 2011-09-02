<?php

class Application_View_Helper_Logout extends Zend_View_Helper_Abstract {

    public function __construct($view, $params) {
        $view->placeholder('logout')->append('<a href="'.$view->url(array('controller'=>'login', 'action'=>'logout')).'">Logout ['.$params['firstName'].' '.$params['lastName'].']</a>');
    }

}
