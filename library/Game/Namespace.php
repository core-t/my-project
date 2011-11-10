<?php

class Game_Namespace{
    protected static $_namespace;
    protected static $_instance = null;
    private function __construct() {
//         new Game_Logger('Singleton NAMESPACE dupa!!!');
        self::$_namespace = new Zend_Session_Namespace();
    }

    static public function getInstance() {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    static public function getNamespace(){
        if (null == self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_namespace;
    }
}
