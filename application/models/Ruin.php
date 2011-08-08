<?php

class Application_Model_Ruin extends Warlords_Db_Table_Abstract
{
    protected $_name = 'ruin';
    protected $_primary = 'ruinId';
    protected $_db;

    public function __construct($gameId) {
        $this->_gameId = $gameId;
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function ruinExists($ruinId) {
        try {
            $select = $this->_db->select()
                    ->from($this->_name, $this->_primary)
                    ->where('"'.$this->_primary.'" = ?', $ruinId)
                    ->where('"gameId" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            if(isset($result[0][$this->_primary])){
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function addRuin($ruinId) {
        $data = array(
            'ruinId' => $ruinId,
            'gameId' => $this->_gameId
        );
        $this->_db->insert($this->_name, $data);
    }
}

