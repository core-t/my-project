<?php

class Application_Model_Chat extends Game_Db_Table_Abstract {

    protected $_name = 'chat';
    protected $_db;

    public function __construct() {
        parent::__construct();
    }

    public function addChat($playerId, $gameId, $msg) {

        $data = array(
            'playerId' => $playerId,
            'gameId' => $gameId,
            'message' => $msg
        );

        return $this->_db->insert($this->_name, $data);
    }

    public function getChat() {

    }

}