<?php

/**
 * Description of Model
 *
 * @author brzoza
 */
abstract class Game_Model {
        public function __construct( $aParams ) {

        $aParams = (array)$aParams;
        foreach( $aParams as $key=>$val ) {
            $keyName = '_'. $key;
            $this->$keyName = $val;
        }

    }

    public function __get($key) {
        $keyName = '_' . $key;
        if (isset($this->$keyName)) {
            return $this->$keyName;
        } else {
            throw new Exception('Brak pola ' . $key );
        }
    }
}

?>
