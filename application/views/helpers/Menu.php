<?php

class Application_View_Helper_Menu extends Zend_View_Helper_Abstract {

    public function __construct($view, $params) {
        $view->placeholder('mainMenu')->append('
<div>
    <ul>
        <li>
            <a href="'.$view->url(array('controller'=>'index', 'action'=>null)).'" class="button">Home</a>
        </li>
        <li>
            <a href="'.$view->url(array('controller'=>'new', 'action'=>null)).'" class="button">New game</a>
        </li>
        <li>
            <a href="'.$view->url(array('controller'=>'load', 'action'=>null)).'" class="button">Load game</a>
        </li>
        <li>
            <a href="'.$view->url(array('controller'=>'hero', 'action'=>null)).'" class="button">Hero</a>
        </li>
        <li>
            <a href="'.$view->url(array('controller'=>'stats', 'action'=>null)).'" class="button">Stats</a>
        </li>
        <li>
            <a href="'.$view->url(array('controller'=>'editor', 'action'=>null)).'" class="button">Map editor</a>
        </li>
        <!--<li>
            <a href="'.$view->url(array('controller'=>'market', 'action'=>null)).'" class="button">Market</a>
        </li>-->
    </ul>
</div>
        ');
    }

}
