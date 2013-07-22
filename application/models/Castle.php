<?php

class Application_Model_Castle extends Game_Db_Table_Abstract {

    protected $_name = 'castlesingame';
    protected $_primary = 'castleId';
    protected $_db;
    protected $_gameId;

    public function __construct($gameId) {
        $this->_gameId = $gameId;
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function getRazedCastles() {
        $castles = array();
        try {
            $select = $this->_db->select()
                    ->from($this->_name)
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('razed = true');
            $result = $this->_db->query($select)->fetchAll();
            foreach ($result as $key => $val)
            {
                $castles[$val['castleId']] = $val;
            }
            return $castles;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getPlayerCastles($playerId) {
        $playersCastles = array();
        $select = $this->_db->select()
                ->from($this->_name, array('production', 'productionTurn', 'defenseMod', 'castleId'))
                ->where('"playerId" = ?', $playerId)
                ->where('"gameId" = ?', $this->_gameId)
                ->where('razed = false');
        try {
            $result = $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($e . $select->__toString());
        }

        foreach ($result as $val)
        {
            $playersCastles[$val['castleId']] = $val;
            unset($playersCastles[$val['castleId']]['castleId']);
        }
        return $playersCastles;
    }

    public function addCastle($id, $playerId) {
        $data = array(
            'castleId' => $id,
            'playerId' => $playerId,
            'gameId' => $this->_gameId
        );
        return $this->_db->insert($this->_name, $data);
    }

    public function isPlayerCastle($castleId, $playerId) {
        try {
            $select = $this->_db->select()
                    ->from($this->_name, $this->_primary)
                    ->where('razed = false')
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" = ?', $playerId)
                    ->where('"castleId" = ?', $castleId);
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0][$this->_primary])) {
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

}
