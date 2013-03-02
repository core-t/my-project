<?php

abstract class Game_Controller_Gui extends Game_Controller_Action
{

    public function init()
    {
        parent::init();

        if (empty($this->_namespace->player['playerId'])) {
            $this->_redirect($this->view->url(array('controller' => 'login')));
        }

        $this->view->headLink()->prependStylesheet($this->view->baseUrl() . '/css/main.css?v=' . Zend_Registry::get('config')->version);
        $this->view->headScript()->prependFile($this->view->baseUrl() . '/js/jquery.js');
        $this->view->Logout($this->_namespace->player);
        $this->view->MainMenu();
        $this->view->googleAnalytics();
        $this->view->Version();

        $this->view->headMeta()->appendHttpEquiv('Content-Language', Zend_Registry::get('lang'));
    }

}
