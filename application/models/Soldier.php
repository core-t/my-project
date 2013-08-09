<?php

class Application_Model_Soldier extends Game_Db_Table_Abstract
{
    protected $_name = 'soldier';
    protected $_primary = 'soldierId';
    protected $_gameId;
    protected $_mapUnits = 'mapunits';

    public function __construct($gameId, $db = null)
    {
        $this->gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function add($armyId, $unitId)
    {
        $units = Zend_Registry::get('units');

        $data = array(
            'armyId' => $armyId,
            'gameId' => $this->gameId,
            'unitId' => $unitId,
            'movesLeft' => $units[$unitId]['numberOfMoves']
        );

        try {
            return $this->db->insert($this->_name, $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    public function resetMovesLeft($playerId)
    {
        $subSelect = $this->db->select()
            ->from('army', 'armyId')
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false')
            ->where('"gameId" = ?', $gameId);

        $select = $this->db->select()
            ->from('soldier', array('movesLeft', 'soldierId', 'unitId'))
            ->where('"armyId" IN (?)', new Zend_Db_Expr($subSelect->__toString()))
            ->where('"gameId" = ?', $gameId);

        try {
            $soldiers = $this->db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());

            return;
        }

        foreach ($soldiers as $soldier) {
            if ($soldier['movesLeft'] > 2) {
                $soldier['movesLeft'] = 2;
            }
            $select = $this->db->select()
                ->from('unit', new Zend_Db_Expr('"numberOfMoves" + ' . $soldier['movesLeft']))
                ->where('"unitId" = ?', $soldier['unitId']);
            $data = array(
                'movesLeft' => new Zend_Db_Expr('(' . $select->__toString() . ')')
            );
            $where = array(
                $this->db->quoteInto('"soldierId" = ?', $soldier['soldierId']),
                $this->db->quoteInto('"gameId" = ?', $gameId)
            );
            self::update('soldier', $data, $where, $this->db, true);
        }
    }

    public function getForBattle($ids)
    {
//        $units = Zend_Registry::get('units');

        $select = $this->db->select()
            ->from(array('a' => $this->_name), array('soldierId', 'unitId'))
            ->join(array('b' => $this->_mapUnits), 'a."unitId" = b."unitId"', array('attackPoints', 'defensePoints', 'canFly', 'canSwim', 'unitId', 'name'))
            ->where('"gameId" = ?', $this->gameId)
            ->where('"armyId" IN (?)', $ids)
            ->order(array('canFly', 'attackPoints', 'defensePoints', 'numberOfMoves', 'a.unitId'));

        try {
            $soldiers = $this->db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }

//        foreach($soldiers as $soldier){
//
//        }

        return $soldiers;
    }

    public function getForWalk($armyId)
    {
        $select = $this->db->select()
            ->from(array('a' => $this->_name), array('movesLeft', 'soldierId', 'unitId'))
            ->join(array('b' => $this->_mapUnits), 'a."unitId" = b."unitId"', array(''))
            ->where('"gameId" = ?', $this->gameId)
            ->where('"armyId" = ?', $armyId)
            ->order(array('canFly', 'attackPoints', 'defensePoints', 'numberOfMoves', 'a.unitId'));

        try {
            return $this->db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    public function calculateCostsOfSoldiers($playerId)
    {
        $units = Zend_Registry::get('units');

        $select1 = $this->db->select()
            ->from('army', 'armyId')
            ->where('"gameId" = ?', $this->gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false');

        $select = $this->db->select()
            ->from($this->_name, 'unitId')
            ->where('"gameId" = ?', $this->gameId)
            ->where('"armyId" IN (?)', new Zend_Db_Expr($select1));

        try {
            $soldiers = $this->db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());

            return;
        }

        $costs = 0;

        foreach ($soldiers as $soldier) {
            $costs += $units[$soldier['unitId']]['cost'];
        }

        return $costs;
    }

         public function getSwimmingSoldiersFromArmiesIds($ids)
    {
        $select = $this->db->select()
            ->from(array('a' => 'soldier'), null)
            ->join(array('b' => 'unit'), 'a."unitId" = b."unitId"', 'canSwim')
            ->where('"canSwim" = true')
            ->where('"gameId" = ?', $this->gameId)
            ->where('"armyId" IN (?)', new Zend_Db_Expr($ids));
        try {
            return $this->db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }
}

