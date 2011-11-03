<?php

/**
 *
 *
 * @author brzoza
 */
abstract class Game_Model_Singleton {

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
        $this->_namespace = Game_Namespace::getNamespace();
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
