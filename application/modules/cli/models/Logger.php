<?php

class Cli_Model_Logger {

    public function __construct($val, $txt = null) {
        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../log/computer' . date('Ymd') . '.log');
        $logger = new Zend_Log($writer);
        $logger->setTimestampFormat("H:i:s");
        if (is_array($val)) {
            $output = Zend_Debug::dump($val, null, false);
        } else {
            $output = $val;
        }
        if ($txt) {
            $output = $txt . ' ' . $output;
        }
        $logger->info($output);
    }

    static public function debug($debug) {
        if (true) {
            print_r($debug[1]);
        }
    }

}