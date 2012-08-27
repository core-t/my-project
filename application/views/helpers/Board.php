<?php

class Application_View_Helper_Board extends Zend_View_Helper_Abstract {

    public function __construct($params) {
        $view = new Zend_View();
        $view->placeholder('board')->append('background: url(\'/../img/maps/'.$params['mapId'].'.png\') no-repeat;width:'.$params['width'].'px;height:'.$params['height'].'px;');
    }

}
