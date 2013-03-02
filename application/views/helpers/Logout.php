<?php

class Zend_View_Helper_Logout extends Zend_View_Helper_Abstract
{

    public function Logout($params)
    {
        $this->view->placeholder('logout')->append('<a href="' . $this->view->url(array('controller' => 'login', 'action' => 'logout')) . '" id="logout">' . $this->view->translate('Logout') . ' (' . $params['firstName'] . ' ' . $params['lastName'] . ')</a>');
    }

}
