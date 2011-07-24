<?php

class Application_Model_Castle extends Warlords_Db_Table_Abstract
{
    protected $_name = 'castle';
    protected $_primary = 'castleId';
    protected $_db;
    protected $_gameId;

    public function __construct($gameId) {
        $this->_gameId = $gameId;
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function getPlayerCastles($id) {
        $playersCastles = array();
        try {
            $select = $this->_db->select()
                ->from($this->_name)
                ->where('"playerId" = ?', $id)
                ->where('"gameId" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            foreach($result as $key => $val) {
                $playersCastles[$val['castleId']] = $val;
            }
            return $playersCastles;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function addCastle($id, $playerId) {
        $data = array(
            'castleId' => $id,
            'playerId' => $playerId,
            'gameId' => $this->_gameId
        );
        $this->_db->insert($this->_name, $data);
    }

    public function deleteCastle($castleId) {
        $where[] = $this->_db->quoteInto('"castleId" = ?', $castleId);
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        $this->_db->delete($this->_name, $where);
    }

    public function isEnemyCastle($castleId, $playerId) {
        try {
            $select = $this->_db->select()
                    ->from($this->_name, $this->_primary)
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"playerId" != ?', $playerId)
                    ->where('"castleId" = ?', $castleId);
            $result = $this->_db->query($select)->fetchAll();
            if(isset ($result[0][$this->_primary])) {
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function setCastleProduction($castleId, $unitId, $playerId) {
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"castleId" = ?', $castleId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $data = array(
            'production' => $unitId,
            'productionTurn' => 0
        );
        return $this->_db->update($this->_name, $data, $where);
    }

}
