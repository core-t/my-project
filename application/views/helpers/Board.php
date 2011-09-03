<?php

class Application_View_Helper_Board extends Zend_View_Helper_Abstract {

    public function __construct($view, $params) {
        $view->placeholder('board')->append('background: url(\'/../img/maps/'.$params['mapId'].'.png\') no-repeat;width:'.$params['mapWidth'].'px;height:'.$params['mapHeight'].'px;');
    }

}
