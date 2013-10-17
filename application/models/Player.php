<?php

class Application_Model_Player extends Game_Db_Table_Abstract
{

    protected $_name = 'player';
    protected $_primary = 'playerId';
    protected $_sequence = 'player_playerId_seq';
    protected $_playerId;

    public function __construct($db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function auth($login, $password)
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_primary)
            ->where('login = ?', $login)
            ->where('password = ?', md5($password));

        try {
            $result = $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }

        if (isset($result[0][$this->_primary])) {
            return $result[0][$this->_primary];
        }
    }

    public function noPlayer()
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_primary)
            ->where('"fbId" = ?', $this->fbid);
        $result = $this->_db->query($select)->fetchAll();
        if (empty($result[0][$this->_primary]))
            return true;
    }

    public function createPlayer($data)
    {
        $this->insert($data);
        return $this->_db->lastSequenceId($this->_db->quoteIdentifier($this->_sequence));
    }

    public function createComputerPlayer()
    {
        $data = array(
            'firstName' => 'Computer',
            'lastName' => 'Player',
            'computer' => 'true',
            'adminId' => 1
        );
        return $this->createPlayer($data);
    }

    public function getPlayer($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name);
        if ($playerId) {
            $select->where('"' . $this->_primary . '" = ?', $playerId);
        } elseif ($this->fbid) {
            $select->where('"fbId" = ?', $this->fbid);
        }
        $result = $this->_db->query($select)->fetchAll();
        return $result[0];
    }

    public function updateFacebookData($data)
    {
        $where = $this->_db->quoteInto('"fbId" = ?', $this->fbid);
        return $this->_db->update($this->_name, $data, $where);
    }

    public function updatePlayer($data, $playerId)
    {
        $where = $this->_db->quoteInto('"playerId" = ?', $playerId);
        return $this->_db->update($this->_name, $data, $where);
    }

//    public function isComputer($playerId)
//    {
//        $select = $this->_db->select()
//            ->from($this->_name, 'computer')
//            ->where('"' . $this->_primary . '" = ?', $playerId);
//        $result = $this->_db->query($select)->fetchAll();
//        return $result[0]['computer'];
//    }

}

