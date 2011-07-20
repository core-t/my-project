<?php

class Application_Model_Hero extends Warlords_Db_Table_Abstract
{
    protected $_name = 'hero';
    protected $_primary = 'heroId';
    protected $_db;
    protected $_id;
    protected $_playerId;

    public function __construct($playerId, $heroId = 0) {
        $this->_playerId = $playerId;
        $this->_id = $heroId;
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function createHero() {
        $data = array(
            'playerId'=> $this->_playerId
        );
        $this->_db->insert($this->_name, $data);
    }

    public function getHeroes() {
        $select = $this->_db->select()
                ->from($this->_name)
                ->where('"playerId" = ?', $this->_playerId);
        return $this->_db->query($select)->fetchAll();
    }
}

