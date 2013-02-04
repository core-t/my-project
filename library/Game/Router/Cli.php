<?php

class Game_Router_Cli extends Zend_Controller_Router_Abstract {

    public function route(Zend_Controller_Request_Abstract $dispatcher) {
        $getopt = new Zend_Console_Getopt(array());
        $arguments = $getopt->getRemainingArgs();

        $dispatcher->setModuleName('cli');

        if ($arguments) {
            $command = array_shift($arguments);
            $action = array_shift($arguments);
            if (!preg_match('~\W~', $command)) {
                $dispatcher->setControllerName($command);
                $dispatcher->setActionName($action);
                $dispatcher->setParams($arguments);
            }

            echo "Invalid command.\n", exit;
        }

        return $dispatcher;
    }

    public function assemble($userParams, $name = null, $reset = false, $encode = true) {
        echo "Not implemented\n", exit;
    }

}
