<?php

abstract class Game_Db_Table_Abstract extends Zend_Db_Table_Abstract {

    public function __construct ( $config = null ) {

        parent::__construct ( $config );

        $this->logActivity();
    }

    protected  function logActivity (){
        $activity = new Game_Player_Activity();
        $activity->logActivity();
    }
    
    protected  function logRequest(){
        $activity = new Game_Player_Request();
//        $activity->logRequest();
    }
}
