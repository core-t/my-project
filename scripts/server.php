<?php

defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(__DIR__ . '/../application'));
defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));
require_once 'Zend/Application.php';
$application = new Zend_Application(
                APPLICATION_ENV,
                APPLICATION_PATH . '/configs/application.ini'
);

//// bootstrap and retrieve the frontController resource
//$front = $application->getBootstrap()
//        ->bootstrap('frontController')
//        ->getResource('frontController');
//
////Which part of the app we want to use?
//$module = 'cli'; //or other module
//$controller = null;
//$action = null;
//$options = array();
//
////create the request
//$request = new Zend_Controller_Request_Simple($action, $controller, $module, $options);
//
//// set front controller options to make everything operational from CLI
//$front->setRequest($request)
//        ->setResponse(new Zend_Controller_Response_Cli())
//        ->setRouter(new Game_Router_Cli())
//        ->throwExceptions(true);

$application->getBootstrap()->bootstrap(array('date', 'config', 'modules', 'frontController'));

declare(ticks = 1);

// Start server
$server = new Cli_WofSocketServer();
$server->run();

exit;
