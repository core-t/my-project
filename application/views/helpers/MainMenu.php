<?php

class zend_View_Helper_MainMenu extends Zend_View_Helper_Abstract {

    public function mainMenu() {
        $this->view->placeholder('mainMenu')->append('
<div>
    <ul>
        <li>
            <a href="/index" class="button">Home</a>
        </li>
        <li>
            <a href="/new" class="button">New game</a>
        </li>
        <li>
            <a href="/load" class="button">Load game</a>
        </li>
        <li>
            <a href="/hero" class="button">Hero</a>
        </li>
        <!--<li>
            <a href="/stats" class="button">Stats</a>
        </li>
        <li>
            <a href="/editor" class="button">Map editor</a>
        </li>
        <li>
            <a href="/market" class="button">Market</a>
        </li>-->
    </ul>
</div>
        ');
    }

}
