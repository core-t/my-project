<?php

class Application_Model_PlayersInGame extends Game_Db_Table_Abstract
{
    protected $_name = 'playersingame';
//    protected $_primary = 'mapPlayerId';
    protected $_sequence = '';
    protected $_gameId;

    public function __construct($gameId, $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getAll()
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('color', 'playerId'))
            ->join(array('b' => 'player'), 'a."playerId" = b."playerId"', array('computer'))
            ->where('a."gameId" = ?', $this->_gameId)
            ->where('color IS NOT NULL');

        return $this->selectAll($select);
    }
}

