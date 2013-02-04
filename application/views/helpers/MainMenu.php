<?php

class zend_View_Helper_MainMenu extends Zend_View_Helper_Abstract {

    public function mainMenu() {
        $this->view->placeholder('mainMenu')->append('
<div>
    <ul>
        <li>
            <a href="/index" class="button">' . $this->view->translate('Home') . '</a>
        </li>
        <li>
            <a href="/new" class="button">' . $this->view->translate('New game') . '</a>
        </li>
        <li>
            <a href="/load" class="button">' . $this->view->translate('Load game') . '</a>
        </li>
        <li>
            <a href="/hero" class="button">' . $this->view->translate('Hero') . '</a>
        </li>
        <!--<li>
            <a href="/stats" class="button">' . $this->view->translate('Stats') . '</a>
        </li>
        <li>
            <a href="/editor" class="button">' . $this->view->translate('Map editor') . '</a>
        </li>
        <li>
            <a href="/market" class="button">' . $this->view->translate('Market') . '</a>
        </li>-->
    </ul>
</div>
        ');
    }

}
