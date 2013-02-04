<?php

class Admin_View_Helper_Menu extends Zend_View_Helper_Abstract {

    public function menu() {
        $this->view->placeholder('mainmenu')
                ->setPrefix("<ul>\n<li>")
                ->setSeparator("</li>\n<li>")
                ->setPostfix("</li>\n</ul>");
        $this->view->placeholder('mainmenu')
                ->append('<a href="/admin" >Menu</a>');
        $this->view->placeholder('mainmenu')
                ->append('<a href="/admin/login/logout" >Wyloguj [' . Zend_Auth::getInstance()->getIdentity() . ']</a>');
    }

}
