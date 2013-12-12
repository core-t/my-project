<?php

class Zend_View_Helper_Map extends Zend_View_Helper_Abstract
{

    public function map($mapId)
    {

        $mMap = new Application_Model_Map($mapId);
        $map = $mMap->getMap();

        $miniWidth = $map['mapWidth'] / 20;
        $miniHeight = $map['mapHeight'] / 20;

        $this->view->placeholder('map')->append('<img id="mapImage" src="/img/maps/' . $map['mapId'] . '.png?v=' . Zend_Registry::get('config')->version . '" width="' . $miniWidth . 'px" height="' . $miniHeight . '"/>');
        $this->view->placeholder('board')->append('<div id="board" style="background: url(\'/img/maps/' . $map['mapId'] . '.png?v=' . Zend_Registry::get('config')->version . '\') no-repeat;width: ' . $map['mapWidth'] . 'px;height:' . $map['mapHeight'] . 'px;"></div>');
    }

}
