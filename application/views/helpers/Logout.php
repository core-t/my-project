<?php

class Zend_View_Helper_Logout extends Zend_View_Helper_Abstract
{

    public function Logout($params)
    {
        $this->view->placeholder('logout')->append('<a href="/' . Zend_Registry::get('lang') . '/login/logout" id="logout" class="button">' . $this->view->translate('Logout') . ' (' . $params['firstName'] . ' ' . $params['lastName'] . ')</a>');
    }

}
