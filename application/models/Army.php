<?php

class Application_Model_Army extends Game_Db_Table_Abstract
{

    protected $_name = 'army';
    protected $_primary = 'armyId';
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

    public function createArmy($position, $playerId, $sleep = 0)
    {
        $armyId = $this->getNewArmyId();
        $data = array(
            'armyId' => $armyId,
            'playerId' => $playerId,
            'gameId' => $this->_gameId,
            'x' => $position['x'],
            'y' => $position['y']
        );
        try {
            $this->_db->insert($this->_name, $data);
            return $armyId;
        } catch (Exception $e) {
            if ($sleep > 10) {
                throw new Exception($e->getMessage());
            }
            sleep(rand(0, $sleep));
            $armyId = $this->createArmy($position, $playerId, $sleep + 1);
        }
        return $armyId;
    }

    private function getNewArmyId()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'max("armyId")')
            ->where('"gameId" = ?', $this->_gameId);
        try {
            return $this->_db->fetchOne($select) + 1;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getPlayerArmies($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('armyId', 'fortified', 'x', 'y'))
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false');

        try {
            $result = $this->_db->query($select)->fetchAll();
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }

        $array = array();

        foreach ($result as $army) {
            $array['army' . $army['armyId']] = $army;
            $array['army' . $army['armyId']]['heroes'] = $this->getArmyHeroes($army['armyId']);
            $array['army' . $army['armyId']]['soldiers'] = $this->getArmySoldiers($army['armyId']);
            if (empty($array['army' . $army['armyId']]['heroes']) AND empty($array['army' . $army['armyId']]['soldiers'])) {
                $this->destroyArmy($array['army' . $army['armyId']]['armyId'], $playerId);
                unset($array['army' . $army['armyId']]);
            }
        }

        return $array;
    }

    private function getArmyHeroes($armyId, $in = false)
    {
        $select = $this->_db->select()
            ->from(array('a' => 'hero'), array('heroId', 'numberOfMoves', 'attackPoints', 'defensePoints', 'name'))
            ->join(array('b' => 'heroesingame'), 'a."heroId" = b."heroId"', array('movesLeft'))
            ->where('"gameId" = ?', $this->_gameId)
            ->order('attackPoints DESC', 'defensePoints DESC', 'numberOfMoves DESC');
        try {
            if ($in) {
                $select->where('"' . $this->_primary . '" IN (?)', new Zend_Db_Expr($armyId));
            } else {
                $select->where('"' . $this->_primary . '" = ?', $armyId);
            }
            $result = $this->_db->query($select)->fetchAll();
            foreach ($result as $k => $row) {
                $mInventory = new Application_Model_Inventory($this->_gameId);
                $result[$k]['artefacts'] = $mInventory->getArtifactsByHeroId($row['heroId']);
            }
            return $result;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    private function getArmySoldiers($armyId)
    {
        $select = $this->_db->select()
            ->from(array('a' => 'soldier'), array('movesLeft', 'soldierId'))
            ->join(array('b' => 'unit'), 'a."unitId" = b."unitId"', 'unitId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"' . $this->_primary . '" = ?', $armyId)
            ->order(array('canFly', 'attackPoints', 'defensePoints', 'numberOfMoves', 'a.unitId'));
        try {
            return $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function destroyArmy($armyId, $playerId)
    {
        $data = array(
            'destroyed' => 'true'
        );

        $where[] = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $armyId);
        $where[] = $this->_db->quoteInto('"gameId" = ?', $this->_gameId);
        $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);

        return $this->_db->update($this->_name, $data, $where);
    }

    public function addHeroToGame($armyId, $heroId)
    {
        $data = array(
            'heroId' => $heroId,
            'armyId' => $armyId,
            'gameId' => $this->_gameId,
            'movesLeft' => 16
        );
        return $this->_db->insert('heroesingame', $data);
    }

    public function allArmiesReady()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'count(*) as number')
            ->where('"gameId" = ?', $this->_gameId);
        try {
            $numberOfArmies = $this->_db->fetchOne($select);
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }

        $select = $this->_db->select()
            ->from('playersingame', 'count(*) as number')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('color IS NOT NULL');
        try {
            $numberOfPlayers = $this->_db->fetchOne($select);
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }

        if ($numberOfArmies >= $numberOfPlayers) {
            return true;
        }
    }

    public function getSelectForPlayerAll($playerId)
    {
        return $this->_db->select()
            ->from($this->_name, 'armyId')
            ->where('destroyed = false')
            ->where('"playerId" = ?', $playerId)
            ->where('"gameId" = ?', $this->_gameId);
    }
}

