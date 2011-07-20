<?php

abstract class Warlords_Db_Table_Abstract extends Zend_Db_Table_Abstract {

    public function __construct ( $config = null ) {
        
        parent::__construct ( $config );
        
        $this->logActivity();
    }

    protected function setNamespace () {
        Zend_Session::start ();
        $this->_namespace = new Zend_Session_Namespace();
    }
    protected  function logActivity (){
        $activity = new Warlords_Player_Activity();
        $activity->logActivity();
        //var_dump($activity->isActive());
    }
}
