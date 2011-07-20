<?php

/**
 * 
 *
 * @author brzoza
 */
abstract class Warlords_Model_Singleton {

    protected $_db = null;
    protected $_namespace = null;

    protected function init () {
        $this->setConnection ();
        $this->setNamespace ();
    }

    public function setConnection ( $connection = null ) {
        if ( NULL === $connection ) {
            $this->_db = Zend_Db_Table_Abstract::getDefaultAdapter ();
            return $this;
        }
        $this->_db = $connection;
        return $this;
    }

    public function setNamespace () {
        Zend_Session::start ();
        $this->_namespace = new Zend_Session_Namespace();
    }

    public function setParams ( $aParams ) {
        $aParams = (array) $aParams;
        foreach ( $aParams as $key => $val ) {
            $keyName = '_' . $key;
            $this->$keyName = $val;
        }
    }

    public function __get ( $key ) {
        $keyName = '_' . $key;
        if ( isset ( $this->$keyName ) ) {
            return $this->$keyName;
        } else {
            throw new Exception ( 'Brak pola ' . $key );
        }
    }

}

?>
