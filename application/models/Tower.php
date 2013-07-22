<?php

class Application_Model_Tower extends Game_Db_Table_Abstract
{
    protected $_name = 'tower';
    protected $_primary = 'towerId';
    protected $_db;

    public function __construct($gameId) {
        $this->_gameId = $gameId;
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function getTowers(){
        try {
            $select = $this->_db->select()
                ->from(array('a' => $this->_name), $this->_primary)
                ->join(array('b' => 'playersingame'), 'a."playerId" = b."playerId" AND a."gameId" = b."gameId"', 'color')
                ->where('a."gameId" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            $towers = array();
            foreach($result as $k=>$row){
                $towers[$row['towerId']] = $row['color'];
            }
            return $towers;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getTower($towerId){
        try {
            $select = $this->_db->select()
                ->from(array('a' => $this->_name), $this->_primary)
                ->join(array('b' => 'playersingame'), 'a."playerId" = b."playerId" AND a."gameId" = b."gameId"', 'color')
                ->where('"'.$this->_primary.'" = ?', $towerId)
                ->where('a."gameId" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            return $result[0];
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }
}

