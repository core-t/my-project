<?php

class Application_View_Helper_Menu extends Zend_View_Helper_Abstract {

    public function __construct($view, $params) {
        $view->placeholder('mainMenu')->append('
<div>
    <ul>
        <li>
            <a href="'.$view->url(array('controller'=>'new')).'" class="button">New game</a>
        </li>
        <li>
            <a href="'.$view->url(array('controller'=>'load')).'" class="button">Load game</a>
        </li>
        <li>
            <a href="'.$view->url(array('controller'=>'hero')).'" class="button">Hero</a>
        </li>
        <li>
            <a href="'.$view->url(array('controller'=>'stats')).'" class="button">Stats</a>
        </li>
        <li>
            <a href="'.$view->url(array('controller'=>'editor')).'" class="button">Map editor</a>
        </li>
        <!--<li>
            <a href="'.$view->url(array('controller'=>'market')).'" class="button">Market</a>
        </li>-->
    </ul>
</div>
        ');
    }

}
