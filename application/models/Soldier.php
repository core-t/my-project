<?php

class Application_Model_Soldier extends Game_Db_Table_Abstract
{
    protected $_name = 'soldier';
    protected $_primary = 'soldierId';
    protected $gameId;
    protected $_mapUnits = 'mapunits';

    public function __construct($gameId, $db = null)
    {
        $this->_gameId = $gameId;
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
            'gameId' => $this->_gameId,
            'unitId' => $unitId,
            'movesLeft' => $units[$unitId]['numberOfMoves']
        );

        return $this->insert($data);
    }

    public function resetMovesLeft($subSelect)
    {
        $select = $this->_db->select()
            ->from('soldier', array('movesLeft', 'soldierId', 'unitId'))
            ->where('"armyId" IN (?)', new Zend_Db_Expr($subSelect->__toString()))
            ->where('"gameId" = ?', $this->_gameId);

        $soldiers = $this->selectAll($select);

        $units = Zend_Registry::get('units');

        foreach ($soldiers as $soldier) {
            if ($soldier['movesLeft'] > 2) {
                $soldier['movesLeft'] = 2;
            }

            $data = array(
                'movesLeft' => $units[$soldier['unitId']]['numberOfMoves'] + $soldier['movesLeft']
            );

            $where = array(
                $this->_db->quoteInto('"soldierId" = ?', $soldier['soldierId']),
                $this->_db->quoteInto('"gameId" = ?', $this->_gameId)
            );

            $this->setQuiet(true);
            $this->update($data, $where);
        }
    }

    public function getForBattle($ids)
    {
//        $units = Zend_Registry::get('units');

        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('soldierId', 'unitId'))
            ->join(array('b' => $this->_mapUnits), 'a."unitId" = b."mapUnitId"', array('attackPoints', 'defensePoints', 'canFly', 'canSwim', 'name'))
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"armyId" IN (?)', $ids)
            ->order(array('canFly', 'attackPoints', 'defensePoints', 'numberOfMoves', 'a.unitId'));

        $soldiers = $this->selectAll($select);

//        foreach($soldiers as $soldier){
//
//        }

        return $soldiers;
    }

    public function getForArmyPosition($armyId)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('movesLeft', 'soldierId', 'unitId'))
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"armyId" = ?', $armyId);
        return $this->selectAll($select);
    }

    public function getForWalk($armyId)
    {
        $select = $this->_db->select()
            ->from(array('a' => $this->_name), array('movesLeft', 'soldierId', 'unitId'))
            ->join(array('b' => $this->_mapUnits), 'a."unitId" = b."mapUnitId"', array(''))
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"armyId" = ?', $armyId)
            ->order(array('canFly', 'attackPoints', 'defensePoints', 'numberOfMoves', 'a.unitId'));

        return $this->selectAll($select);
    }

    public function getSoldiers($armyId)
    {
        $select = $this->_db->select()
            ->from('soldier', 'unitId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"armyId" = ?', $armyId);

        return $this->selectAll($select);
    }

    public function calculateCostsOfSoldiers($subSelect)
    {
        $units = Zend_Registry::get('units');

        $select = $this->_db->select()
            ->from($this->_name, 'unitId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"armyId" IN (?)', new Zend_Db_Expr($subSelect->__toString()));

        $soldiers = $this->selectAll($select);

        $costs = 0;

        foreach ($soldiers as $soldier) {
            $costs += $units[$soldier['unitId']]['cost'];
        }

        return $costs;
    }

    public function getSwimmingFromArmiesIds($ids)
    {
        $units = Zend_Registry::get('units');
        $canSwimIds = '';

        foreach ($units as $unit) {
            if ($unit['canSwim']) {
                if ($canSwimIds) {
                    $canSwimIds .= ',';
                }
                $canSwimIds .= $unit['unitId'];
            }
        }

        $select = $this->_db->select()
            ->from($this->_name, 'unitId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"unitId" = IN (?)', new Zend_Db_Expr($canSwimIds))
            ->where('"armyId" IN (?)', new Zend_Db_Expr($ids));

        return $this->selectAll($select);
    }

    public function getMaximumMoves($armyId)
    {
        $units = Zend_Registry::get('units');

        $select = $this->_db->select()
            ->from($this->_name, 'unitId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"armyId" = ?', $armyId);

        $soldiers = $this->selectAll($select);

        $moves = 0;

        foreach ($soldiers as $soldier) {
            if ($moves < $units[$soldier['unitId']]['numberOfMoves']) {
                $moves = $units[$soldier['unitId']]['numberOfMoves'];
            }
        }

        return $moves;
    }

    public function destroy($soldierId)
    {
        $where = array(
            $$this->_db->quoteInto('"soldierId" = ?', $soldierId),
            $$this->_db->quoteInto('"gameId" = ?', $$this->_gameId)
        );

        $this->delete($where);
    }
}

