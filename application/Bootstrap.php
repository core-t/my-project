<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initDate()
    {
        date_default_timezone_set('Europe/Warsaw');
    }

    protected function _initDb()
    {
        $resource = $this->getPluginResource('db');
        $db = $resource->getDbAdapter();
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
    }

    protected function _initView()
    {
        $this->bootstrap('layout');
        $layout = $this->getResource('layout');
        $view = $layout->getView();

        $view->addHelperPath(APPLICATION_PATH . '/../library/Coret/View/Helper/');

        $view->doctype('XHTML1_TRANSITIONAL');

        // Set the initial title and separator:
        $view->headTitle('Wars of Fate')->setSeparator(' :: ');

        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8');
        $view->headMeta()->appendName('keywords', 'turn based, strategic, game');
        $view->headMeta()->appendName('description', 'This is a multiplayer strategic game');
        $view->headMeta()->appendName('author', 'Bartosz Krzeszewski');
        $view->headMeta()->appendName('date', '2011');
        $view->headMeta()->appendName('copyright', 'Bartosz Krzeszewski 2011');
//         $view->headMeta()->appendName('google-site-verification', '');
    }

    protected function _initRegisterLogger()
    {
        $logger = new Zend_Log();
        $logger->setTimestampFormat("H:i:s");
        $logger->addWriter(new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../log/' . date('Y-m-d') . '.log'));
        Zend_Registry::set('Zend_Log', $logger);
    }

//    protected function _initRegisterLogger()
//    {
//        $this->bootstrap('Log');
//
//        if (!$this->hasPluginResource('Log')) {
//            throw new Zend_Exception('Log not enabled in application.ini');
//        }
//
//        $logger = $this->getResource('Log');
//        assert($logger != null);
//        Zend_Registry::set('Zend_Log', $logger);
//    }

    protected function _initSession()
    {
        Zend_Session::start();
    }

    protected function _initConfig()
    {
        $config = new Zend_Config($this->getOptions());
        Zend_Registry::set('config', $config);
    }

//    protected function _initRouter() {
//        if (PHP_SAPI == 'cli') {
//            $this->bootstrap('FrontController');
//            $front = $this->getResource('FrontController');
//            $front->setParam('disableOutputBuffering', true);
//            $front->setRouter(new Game_Router_Cli());
//            $front->setRequest(new Zend_Controller_Request_Simple());
//        }
//    }

    public function _initRoutes()
    {
        $this->bootstrap('FrontController');
        $this->_frontController = $this->getResource('FrontController');
        $router = $this->_frontController->getRouter();

        if (isset($_GET['lang'])) {
            $lang = $_GET['lang'];
        } else {
            $locale = new Zend_Locale();
            $lang = $locale->getLanguage();
        }

        $langRoute = new Zend_Controller_Router_Route(
            ':lang/',
            array(
                'lang' => $lang
            )
        );

        $defaultRoute = new Zend_Controller_Router_Route(
            ':controller/:action/*',
            array(
                'module' => 'default',
                'controller' => 'index',
                'action' => 'index'
            )
        );

        $defaultRoute = $langRoute->chain($defaultRoute);

        $adminRoute = new Zend_Controller_Router_Route(
            'admin/:controller/:action/*',
            array(
                'module' => 'admin',
                'controller' => 'index',
                'action' => 'index'
            )
        );

        $router->addRoute('langRoute', $langRoute);
        $router->addRoute('defaultRoute', $defaultRoute);
        $router->addRoute('adminRoute', $adminRoute);
    }

    protected function _initLanguage()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new Coret_Controller_Plugin_Language());
    }

}

