<?php

class Zend_View_Helper_Map extends Zend_View_Helper_Abstract
{

    public function map($mapId)
    {

        $mMap = new Application_Model_Map($mapId);
        $map = $mMap->getMap();

        $miniWidth = $map['mapWidth'] / 20;

        $this->view->placeholder('map')->append('<img id="map" src="/img/maps/' . $map['name'] . '.png" width="' . $miniWidth . 'px"/>');
        $this->view->placeholder('board')->append('<div id="board" style="background: url(\'../img/maps/' . $map['name'] . '.png\') no-repeat;width: ' . $map['mapWidth'] . 'px;height:' . $map['mapHeight'] . 'px;"></div>');
    }

}
