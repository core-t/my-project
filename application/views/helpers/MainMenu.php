<?php

class zend_View_Helper_MainMenu extends Zend_View_Helper_Abstract
{

    public function mainMenu()
    {
        $lang = Zend_Registry::get('lang');

        $this->view->placeholder('mainMenu')->append('
<div>
    <ul>
        <li>
            <a href=" /' . $lang . '/new" class="button">' . $this->view->translate('Play') . '</a>
        </li>
        <li>
            <a href="/' . $lang . '/load" class="button">' . $this->view->translate('Load game') . '</a>
        </li>
        <li>
            <a href="/' . $lang . '/halloffame" class="button">' . $this->view->translate('Hall of Fame') . '</a>
        </li>
        <li>
            <a href="/' . $lang . '/hero" class="button">' . $this->view->translate('Hero') . '</a>
        </li>
        <li>
            <a href="/' . $lang . '/profile" class="button">' . $this->view->translate('Profile') . '</a>
        </li>
        <!--<li>
            <a href="/' . $lang . '/stats" class="button">' . $this->view->translate('Stats') . '</a>
        </li>
        <li>
            <a href="/' . $lang . '/editor" class="button">' . $this->view->translate('Map editor') . '</a>
        </li>
        <li>
            <a href="/' . $lang . '/market" class="button">' . $this->view->translate('Market') . '</a>
        </li>-->
    </ul>
</div>
        ');
    }

}
