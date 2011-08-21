<?php

class Application_Model_Castle extends Game_Db_Table_Abstract
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

    public function playerCastlesExists($playerId){
        try {
            $select = $this->_db->select()
                ->from($this->_name, $this->_primary)
                ->where('"playerId" = ?', $playerId)
                ->where('"gameId" = ?', $this->_gameId)
                ->where('razed = false');
            $result = $this->_db->query($select)->fetchAll();
            if(count($result)){
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getRazedCastles() {
        $castles = array();
        try {
            $select = $this->_db->select()
                ->from($this->_name)
                ->where('"gameId" = ?', $this->_gameId)
                ->where('razed = true');
            $result = $this->_db->query($select)->fetchAll();
            foreach($result as $key => $val) {
                $castles[$val['castleId']] = $val;
            }
            return $castles;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getPlayerCastles($playerId) {
        $playersCastles = array();
        try {
            $select = $this->_db->select()
                ->from($this->_name)
                ->where('"playerId" = ?', $playerId)
                ->where('"gameId" = ?', $this->_gameId)
                ->where('razed = false');
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
        return $this->_db->insert($this->_name, $data);
    }

    public function deleteCastle($castleId) {
        $where[] = $this->_db->quoteInto('"castleId" = ?', $castleId);
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        $this->_db->delete($this->_name, $where);
    }

    public function changeOwner($castleId, $playerId) {
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"castleId" = ?', $castleId);
        $data = array(
            'defenseMod' => new Zend_Db_Expr('"defenseMod" - 1'),
            'playerId' => $playerId,
            'production' => null,
            'productionTurn' => 0,
        );
        return $this->_db->update($this->_name, $data, $where);
    }

    public function razeCastle($castleId, $playerId) {
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"castleId" = ?', $castleId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $data = array(
            'razed' => 'true',
            'production' => null,
            'productionTurn' => 0,
        );
        return $this->_db->update($this->_name, $data, $where);
    }

    public function castleExist($castleId) {
        try {
            $select = $this->_db->select()
                    ->from($this->_name, $this->_primary)
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"castleId" = ?', $castleId);
            $result = $this->_db->query($select)->fetchAll();
            if(isset ($result[0][$this->_primary])) {
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getCastle($castleId) {
        try {
            $select = $this->_db->select()
                    ->from($this->_name)
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"castleId" = ?', $castleId);
            $result = $this->_db->query($select)->fetchAll();
            return $result[0];
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
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

    public function raiseAllCastlesProductionTurn($playerId) {
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $data = array(
            'productionTurn' => new Zend_Db_Expr('"productionTurn" + 1')
        );
        return $this->_db->update($this->_name, $data, $where);
    }

    public function getCastleProduction($castleId, $playerId) {
        try {
            $select = $this->_db->select()
                    ->from($this->_name, array('production', 'productionTurn'))
                    ->where('"gameId" = ?', $this->_gameId)
                    ->where('"'.$this->_primary.'" = ?', $castleId)
                    ->where('"playerId" = ?', $playerId);
            $result = $this->_db->query($select)->fetchAll();
            return $result[0];
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function resetProductionTurn($castleId, $playerId) {
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        $where[] = $this->_db->quoteInto('"castleId" = ?', $castleId);
        $data = array(
            'productionTurn' => 0
        );
        return $this->_db->update($this->_name, $data, $where);
    }

}
