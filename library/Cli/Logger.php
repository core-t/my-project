<?php

class Cli_Logger {

    public function __construct($val, $txt = null) {
        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../log/log.log');
        $logger = new Zend_Log($writer);
        if (is_array($val)) {
            $output = Zend_Debug::dump($val, null, false);
        } else {
            $output = $val;
        }
        if($txt){
            $output = $txt.' '.$output;
        }
        $logger->info($output);
    }

}