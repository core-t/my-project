<?php

class Game_Logger {

    public function __construct($val) {
        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/log/log.html');
        $logger = new Zend_Log($writer);
        if (is_array($val)) {
            $output = Zend_Debug::dump($val, null, false);
        } else {
            $output = $val;
        }
        $logger->info($output);
    }

}