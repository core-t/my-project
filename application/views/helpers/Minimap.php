<?php

class Application_View_Helper_Minimap extends Zend_View_Helper_Abstract {

    public function __construct($view, $params) {
        $width = $params['mapWidth']*2;
        $height = $params['mapHeight']*2;
        $view->placeholder('miniMap')->append('<img id="map" src="/img/maps/'.$params['mapId'].'.png" width="'.$width.'px" height="'.$height.'px">');
    }

}
