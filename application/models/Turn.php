<?php

class Application_Model_Chat extends Coret_Db_Table_Abstract
{

    protected $_name = 'chat';
    protected $_primary = 'chatId';

    public function __construct($gameId, $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getChatHistory()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('date', 'message', 'playerId'))
            ->where('"gameId" = ?', $this->_gameId)
            ->order($this->_primary);
        return $this->selectAll($select);
    }

    public function insertChatMessage($playerId, $message)
    {
        $data = array(
            'message' => $message,
            'playerId' => $playerId,
            'gameId' => $this->_gameId
        );
        $this->insert($data);
    }
}
