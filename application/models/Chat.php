<?php

class Application_Model_Chat extends Game_Db_Table_Abstract {

    protected $_name = 'chat';
    protected $_primary = 'chatId';
    protected $_db;
    protected $_id;

    public function __construct($gameId = 0) {
        $this->_gameId = $gameId;
        parent::__construct();
    }

    public function addChat($playerId, $msg) {

        $data = array(
            'playerId' => $playerId,
            'gameId' => $this->_gameId,
            'message' => $msg
        );

        return $this->_db->insert($this->_name, $data);
    }

    public function getChatHistory() {
        $select = $this->_db->select()
                ->from($this->_name, array('date', 'message', 'playerId'))
                ->where('"gameId" = ?', $this->_gameId)
                ->order($this->_primary);
        return $this->_db->query($select)->fetchAll();
    }

}
