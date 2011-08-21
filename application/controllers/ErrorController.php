<?php

class ErrorController extends Zend_Controller_Action {

    public function errorAction() {
        $this->_helper->layout->disableLayout();
        $errors = $this->_getParam('error_handler');

        $logger = Zend_Registry::getInstance()->get('logger');

        $elementsToLog = array(
            ' TIME:      "' . date("D M j G:i:s T Y") . '"',
            'IN FILE:   "' . $errors->exception->getFile() . '"',
            'ON LINE: "' . $errors->exception->getLine() . '"',
            'TYPE:     "' . $errors->type . '"',
            'MSG:      "' . $errors->exception->getMessage() . '"',
            'REQEST: "' . $this->getRequest()->getRequestUri() . '"',
            "\n" . $errors->exception
        );

        $msg = "\n" . implode(" \n ", $elementsToLog) . "\n*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*L*-*-*O*-*-*G*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*\n";

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                $logger->log($msg, Zend_log::INFO);
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                $logger->log($msg, Zend_log::ERR);
                break;
        }

        $this->view->exception = $errors->exception;
        $this->view->request = $errors->request;
    }

    public function getLog() {
        $bootstrap = $this->getInvokeArg('bootstrap');
        if (!$bootstrap->hasResource('Log')) {
            return false;
        }
        $log = $bootstrap->getResource('Log');
        return $log;
    }

}

