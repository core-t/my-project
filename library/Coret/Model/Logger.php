<?php

class Coret_Model_Logger
{

    private $_logger;

    public function __construct($type = 'computer')
    {
        $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH . '/../log/' . $type . date('Ymd') . '.log');
        $this->_logger = new Zend_Log($writer);
        $this->_logger->setTimestampFormat("H:i:s");
    }

    public function log($val, $txt = null)
    {
        if (is_array($val)) {
            $output = Zend_Debug::dump($val, null, false);
        } else {
            $output = $val;
        }

        if ($txt) {
            $output = $txt . ' ' . $output;
        }

        $this->_logger->info($output);
    }

    static public function debug($debug)
    {
        if (true) {
            print_r($debug[1]);
        }
    }

}