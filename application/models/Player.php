<?php

class Application_Model_Player extends Warlords_Db_Table_Abstract {

    protected $_name = 'player';
    protected $_primary = 'playerId';
    protected $_sequence = 'player_playerId_seq';
    protected $_db;
    protected $_playerId;
    protected $fbid;

    public function __construct($id = 0, $facebook = true) {
        if ($facebook) {
            $this->fbid = $id;
        } else {
            $this->_playerId = $id;
        }
        $this->_db = $this->getDefaultAdapter();

        parent::__construct();
    }

    public function loginExists($login) {
        try {
            $select = $this->_db->select()
                    ->from($this->_name, $this->_primary)
                    ->where('login = ?', $login);
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0][$this->_primary])) {
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function auth($login, $password) {
        try {
            $select = $this->_db->select()
                    ->from($this->_name, $this->_primary)
                    ->where('login = ?', $login)
                    ->where('password = ?', md5($password));
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0][$this->_primary])) {
                return $result[0][$this->_primary];
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function noPlayer() {
        $select = $this->_db->select()
                ->from($this->_name, $this->_primary)
                ->where('"fbId" = ?', $this->fbid);
        $result = $this->_db->query($select)->fetchAll();
        if (empty($result[0][$this->_primary]))
            return true;
    }

    public function createPlayer($data) {
        $this->_db->insert($this->_name, $data);
        $seq = $this->_db->quoteIdentifier($this->_sequence);
        return $this->_db->lastSequenceId($seq);
    }

    public function getPlayer($playerId) {
        $select = $this->_db->select()
                ->from($this->_name);
        if ($playerId) {
            $select->where('"' . $this->_primary . '" = ?', $playerId);
        } elseif($this->fbid) {
            $select->where('"fbId" = ?', $this->fbid);
        }
        $result = $this->_db->query($select)->fetchAll();
        return $result[0];
    }

    public function updatePlayer($data) {
        $where = $this->_db->quoteInto('"fbId" = ?', $this->fbid);
        return $this->_db->update($this->_name, $data, $where);
    }

}

