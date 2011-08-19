<?php

class Application_Model_Player extends Warlords_Db_Table_Abstract {

    protected $_name = 'player';
    protected $_primary = 'playerId';
    protected $_sequence = 'player_playerId_seq';
    protected $_db;
    protected $_playerId;
    protected $fbid;

    public function __construct($id, $facebook = true) {
        if($facebook){
            $this->fbid = $id;
        }else{
            $this->_playerId = $id;
        }
        $this->_db = $this->getDefaultAdapter();

        parent::__construct();
    }

    public function noPlayer() {
        $select = $this->_db->select()
                ->from($this->_name, $this->_primary)
                ->where('"fbId" = ?', $this->fbid);
        $result = $this->_db->query($select)->fetchAll();
        if (empty($result[0][$this->_primary]))
            return true;
    }

    public function createPlayer() {
        $dane = array(
            'fbId' => $this->fbid,
            'activity' => '2011-06-15'
        );
        $this->_db->insert($this->_name, $dane);
        $seq = $this->_db->quoteIdentifier($this->_sequence);
        return $this->_db->lastSequenceId($seq);
    }

    public function getPlayer() {
        $select = $this->_db->select()
                ->from($this->_name)
                ->where('"fbId" = ?', $this->fbid);
        $result = $this->_db->query($select)->fetchAll();
        if (isset($result[0]))
            return $result[0];
    }

    public function updatePlayer($data) {
        $where = $this->_db->quoteInto('"fbId" = ?', $this->fbid);
        return $this->_db->update($this->_name, $data, $where);
    }

}

