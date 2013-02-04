<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initDate() {
        date_default_timezone_set('Europe/Warsaw');
    }

    protected function _initDb() {
        $resource = $this->getPluginResource('db');
        $db = $resource->getDbAdapter();
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
    }

    protected function _initView() {
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
        $view = $layout->getView();

        $view->addHelperPath(APPLICATION_PATH . '/../library/Coret/View/Helper/');

        $view->doctype('XHTML1_STRICT');

        // Set the initial title and separator:
        $view->headTitle('Wars of Fate')->setSeparator(' :: ');

        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8')->appendHttpEquiv('Content-Language', 'pl-PL');
        $view->headMeta()->appendName('keywords', '');
        $view->headMeta()->appendName('description', '');
        $view->headMeta()->appendName('author', 'Bartosz Krzeszewski');
        $view->headMeta()->appendName('date', '2011');
        $view->headMeta()->appendName('copyright', 'Bartosz Krzeszewski 2011');
//         $view->headMeta()->appendName('google-site-verification', '');
    }

    protected function _initLogger() {
        $logger = new Zend_Log();
        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../log/' . date('Y-m-d') . '.log');
        $logger->addWriter($writer);
        Zend_Registry::set('logger', $logger);
    }

    protected function _initSession() {
        Zend_Session::start();
    }

    protected function _initConfig() {
        $config = new Zend_Config($this->getOptions());
        Zend_Registry::set('config', $config);
    }

    protected function _initRouter() {
        if (PHP_SAPI == 'cli') {
            $this->bootstrap('FrontController');
            $front = $this->getResource('FrontController');
            $front->setParam('disableOutputBuffering', true);
            $front->setRouter(new Game_Router_Cli());
            $front->setRequest(new Zend_Controller_Request_Simple());
        }
    }

}

