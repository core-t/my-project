<?php

class Application_Model_Army extends Coret_Db_Table_Abstract
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

        return $this->selectAll($select);
    }

    public function destroyArmy($armyId, $playerId)
    {
        $data = array(
            'destroyed' => 'true'
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier($this->_primary) . ' = ?', $armyId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId),
        );

        return $this->update($data, $where);
    }

    public function getNumberOfArmies()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'count(*) as number')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId);

        return $this->selectOne($select);
    }

    public function getSelectForPlayerAll($playerId)
    {
        return $this->_db->select()
            ->from($this->_name, 'armyId')
            ->where('destroyed = false')
            ->where('"playerId" = ?', $playerId)
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId);
    }

    public function fortify($armyId, $fortify, $playerId = null)
    {
        if ($fortify) {
            $data = array(
                'fortified' => 't'
            );
        } else {
            $data = array(
                'fortified' => 'f'
            );
        }

        $where = array(
            $this->_db->quoteInto('"armyId" = ?', $armyId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
        );

        if ($playerId) {
            $where[] = $this->_db->quoteInto('"playerId" = ?', $playerId);
        }

        return $this->update($data, $where);
    }

    public function updateArmyPosition($playerId, $path, $fields, $army)
    {
        if (empty($path)) {
            return;
        }

        $units = Zend_Registry::get('units');
        $terrain = Zend_Registry::get('terrain');

        if ($army['canFly'] > 0) {
            $type = 'flying';
        } elseif ($army['canSwim']) {
            $type = 'swimming';
        } else {
            $type = 'walking';
        }

        $mHeroesInGame = new Application_Model_HeroesInGame($this->_gameId, $this->_db);

        $heroes = $army['heroes'];

        foreach ($heroes as $hero) {
            $movesSpend = 0;

            foreach ($path as $step) {
                if (!isset($step['myCastleCosts'])) {
                    $movesSpend += $terrain[$fields[$step['y']][$step['x']]][$type];
                }
            }

            $movesLeft = $hero['movesLeft'] - $movesSpend;
            if ($movesLeft < 0) {
                $movesLeft = 0;
            }

            $mHeroesInGame->updateMovesLeft($movesLeft, $hero['heroId']);
        }

        $soldiers = $army['soldiers'];
        $mSoldier = new Application_Model_UnitsInGame($this->_gameId, $this->_db);

        if ($army['canFly'] > 0 || $army['canSwim']) {
            foreach ($soldiers as $soldier) {
                $movesSpend = 0;

                foreach ($path as $step) {
                    if (!isset($step['myCastleCosts'])) {
                        $movesSpend += $terrain[$fields[$step['y']][$step['x']]][$type];
                    }
                }

                $movesLeft = $soldier['movesLeft'] - $movesSpend;
                if ($movesLeft < 0) {
                    $movesLeft = 0;
                }

                $mSoldier->updateMovesLeft($movesLeft, $soldier['soldierId']);
            }
        } else {
            foreach ($soldiers as $soldier) {
                $movesSpend = 0;

                $terrain['f'][$type] = $units[$soldier['unitId']]['modMovesForest'];
                $terrain['m'][$type] = $units[$soldier['unitId']]['modMovesHills'];
                $terrain['s'][$type] = $units[$soldier['unitId']]['modMovesSwamp'];

                foreach ($path as $step) {
                    if (!isset($step['myCastleCosts'])) {
                        $movesSpend += $terrain[$fields[$step['y']][$step['x']]][$type];
                    }
                }

                $movesLeft = $soldier['movesLeft'] - $movesSpend;
                if ($movesLeft < 0) {
                    $movesLeft = 0;
                }

                $mSoldier->updateMovesLeft($movesLeft, $soldier['soldierId']);
            }
        }

        $end = end($path);
        $data = array(
            'x' => $end['x'],
            'y' => $end['y'],
            'fortified' => 'false'
        );
        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier('armyId') . ' = ?', $army['armyId']),
            $this->_db->quoteInto($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId),
            $this->_db->quoteInto($this->_db->quoteIdentifier('playerId') . ' = ?', $playerId)
        );

        return $this->update($data, $where);
    }

    public function getAllEnemiesArmies($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, Cli_Model_Army::armyArray())
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where('"playerId" != ?', $playerId)
            ->where('destroyed = false');

        $result = $this->selectAll($select);

        $armies = array();

        $mSoldier = new Application_Model_UnitsInGame($this->_gameId, $this->_db);
        $mHeroesInGame = new Application_Model_HeroesInGame($this->_gameId, $this->_db);

        foreach ($result as $army) {
            $armies[$army['armyId']] = $army;
            $armies[$army['armyId']]['heroes'] = $mHeroesInGame->getForMove($army['armyId']);

//            foreach ($armies[$army['armyId']]['heroes'] as $k => $row) {
//                $mInventory = new Application_Model_Inventory($row['heroId'], $gameId, $db);
//                $armies[$army['armyId']]['heroes'][$k]['artifacts'] = $mInventory->getAll();
//            }

            $armies[$army['armyId']]['soldiers'] = $mSoldier->getForMove($army['armyId']);
            if (empty($armies[$army['armyId']]['heroes']) AND empty($armies[$army['armyId']]['soldiers'])) {
                $this->destroyArmy($armies[$army['armyId']]['armyId'], $playerId);
                unset($armies[$army['armyId']]);
            } else {
                $armies[$army['armyId']]['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($armies[$army['armyId']]);
            }
        }

        return $armies;
    }

    public function getComputerArmyToMove($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, Cli_Model_Army::armyArray())
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false')
            ->where('fortified = false');

        $result = $this->selectAll($select);

        $mSoldier = new Application_Model_UnitsInGame($this->_gameId, $this->_db);
        $mHeroesInGame = new Application_Model_HeroesInGame($this->_gameId, $this->_db);

        foreach ($result as $army) {
            $army['heroes'] = $mHeroesInGame->getForMove($army['armyId']);
//            foreach ($army['heroes'] as $k => $row) {
//                $mInventory = new Application_Model_Inventory($row['heroId'], $gameId, $db);
//                $army['heroes'][$k]['artifacts'] = $mInventory->getAll();
//            }
            $army['soldiers'] = $mSoldier->getForMove($army['armyId']);
            if (empty($army['heroes']) AND empty($army['soldiers'])) {
                $this->destroyArmy($army['armyId'], $playerId);
            }
            $army['movesLeft'] = Cli_Model_Army::calculateMaxArmyMoves($army);
            return $army;
        }
    }

    public function getDefender($ids)
    {
        if (empty($ids)) {
            return;
        }

        $select = $this->_db->select()
            ->from($this->_name, Cli_Model_Army::armyArray('playerId'))
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"armyId" IN (?)', new Zend_Db_Expr(implode(',', $ids)));

        $result = $this->selectAll($select);

        foreach ($result as $k => $army) {
            unset($result[$k]['playerId']);

            if ($army['destroyed']) {
                continue;
            }

            $mHeroesInGame = new Application_Model_HeroesInGame($this->_gameId, $this->_db);
            $heroes = $mHeroesInGame->getForMove($army['armyId']);

            $mSoldier = new Application_Model_UnitsInGame($this->_gameId, $this->_db);
            $soldiers = $mSoldier->getSoldiers($army['armyId']);

            if (empty($heroes) AND empty($soldiers)) {
                $this->destroyArmy($army['armyId'], $army['playerId']);
                $result[$k]['destroyed'] = true;
            } else {
                $result[$k]['heroes'] = $heroes;
                $result[$k]['soldiers'] = $soldiers;
            }
        }

        return $result;
    }

    public function getArmyByArmyId($armyId)
    {
        $select = $this->_db->select()
            ->from($this->_name, Cli_Model_Army::armyArray('playerId'))
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where('"armyId" = ?', $armyId);

        return $this->selectRow($select);
    }

    public function getPlayerArmiesWithUnits($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, Cli_Model_Army::armyArray())
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false');

        $result = $this->selectAll($select);

        $armies = array();

        $mHeroesInGame = new Application_Model_HeroesInGame($this->_gameId, $this->_db);
        $mSoldier = new Application_Model_UnitsInGame($this->_gameId, $this->_db);

        foreach ($result as $army) {
            $armies[$army['armyId']] = $army;
            $armies[$army['armyId']]['heroes'] = $mHeroesInGame->getForMove($army['armyId']);

            $armies[$army['armyId']]['soldiers'] = $mSoldier->getForMove($army['armyId']);
            if (empty($armies[$army['armyId']]['heroes']) AND empty($armies[$army['armyId']]['soldiers'])) {
                $this->destroyArmy($armies[$army['armyId']]['armyId'], $playerId);
                unset($armies[$army['armyId']]);
            }
        }

        return $armies;
    }

    public function isOtherArmyAtPosition($armyId, $x, $y)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'armyId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"armyId" != ?', $armyId)
            ->where('destroyed = false')
            ->where('x = ?', $x)
            ->where('y = ?', $y);

        return $this->selectOne($select);
    }

    public function unfortifyComputerArmies($playerId)
    {
        $data = array(
            'fortified' => 'false'
        );
        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"playerId" = ?', $playerId),
        );

        return $this->update($data, $where);
    }

    public function playerArmiesExists($playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'armyId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('destroyed = false')
            ->where('"playerId" = ?', $playerId);

        $result = $this->selectAll($select);
        if (count($result)) {
            return true;
        }
    }

    public function getArmyIdFromPosition($position)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'armyId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('destroyed = false')
            ->where('x = ?', $position['x'])
            ->where('y = ?', $position['y']);

        return $this->selectOne($select);
    }

    public function getArmyIdsByPositionPlayerId($position, $playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'armyId')
            ->where($this->_db->quoteIdentifier('gameId') . ' = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false')
            ->where('x = ?', $position['x'])
            ->where('y = ?', $position['y']);

        return $this->selectAll($select);
    }

    public function getPlayerIdFromPosition($playerId, $position)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'playerId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" != ?', $playerId)
            ->where('destroyed = false')
            ->where('x = ?', $position['x'])
            ->where('y = ?', $position['y']);

        return $this->selectOne($select);
    }

    public function getAllEnemyUnitsFromPosition($position, $playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'armyId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" != ?', $playerId)
            ->where('destroyed = false')
            ->where('x = (?)', $position['x'])
            ->where('y = (?)', $position['y']);

        $result = $this->selectAll($select);

        $ids = array();

        foreach ($result as $id) {
            $ids[] = $id['armyId'];
        }

        if ($ids) {
            $mSoldier = new Application_Model_UnitsInGame($this->_gameId, $this->_db);
            $mHeroesInGame = new Application_Model_HeroesInGame($this->_gameId, $this->_db);
            return array(
                'heroes' => $mHeroesInGame->getForBattle($ids),
                'soldiers' => $mSoldier->getForBattle($ids),
                'ids' => $ids
            );
        } else {
            return array(
                'heroes' => null,
                'soldiers' => null,
                'ids' => null
            );
        }
    }

    public function areMySwimmingUnitsAtPosition($position, $playerId)
    {
        $ids = '';

        $select = $this->_db->select()
            ->from($this->_name, 'armyId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false')
            ->where('x = (?)', $position['x'])
            ->where('y = (?)', $position['y']);

        $result = $this->selectAll($select);

        foreach ($result as $id) {
            if ($ids) {
                $ids .= ',';
            }
            $ids .= $id['armyId'];
        }

        if (!$ids) {
            return;
        }

        $mSoldier = new Application_Model_UnitsInGame($this->_gameId, $this->_db);

        return $mSoldier->getSwimmingFromArmiesIds($ids);
    }

    public function getArmyPositionByArmyIdPlayerId($armyId, $playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, array('x', 'y'))
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false')
            ->where('"armyId" = ?', $armyId);

        return $this->selectRow($select);
    }

    public function areUnitsAtCastlePosition($position)
    {
        $xs = array(
            $position['x'],
            $position['x'] + 1
        );
        $ys = array(
            $position['y'],
            $position['y'] + 1
        );
        $select = $this->_db->select()
            ->from($this->_name, 'armyId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('destroyed = false')
            ->where('x IN (?)', $xs)
            ->where('y IN (?)', $ys);

        return $this->selectAll($select);
    }

    public function getAllPlayerArmiesExceptOne($armyId, $playerId)
    {

        $select = $this->_db->select()
            ->from($this->_name, array('x', 'y'))
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"armyId" != ?', $armyId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false');

        return $this->selectAll($select);
    }

    public function getEnemyArmiesFieldsPositions($playerId)
    {
        $fields = Zend_Registry::get('fields');

        $select = $this->_db->select()
            ->from($this->_name, array('x', 'y'))
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" != ?', $playerId)
            ->where('destroyed = false');

        foreach ($this->selectAll($select) as $row) {
            $fields[$row['y']][$row['x']] = 'e';
        }

        return $fields;
    }

    public function getArmyByArmyIdPlayerId($armyId, $playerId)
    {
        $select = $this->_db->select()
            ->from($this->_name, Cli_Model_Army::armyArray())
            ->where('"gameId" = ?', $this->_gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false')
            ->where('"armyId" = ?', $armyId);

        return $this->selectRow($select);
    }

    public function getCastleGarrisonFromCastlePosition($position)
    {
        $xs = array(
            $position['x'],
            $position['x'] + 1
        );

        $ys = array(
            $position['y'],
            $position['y'] + 1
        );

        $ids = array();

        $select = $this->_db->select()
            ->from($this->_name, 'armyId')
            ->where('"gameId" = ?', $this->_gameId)
            ->where('destroyed = false')
            ->where('x IN (?)', $xs)
            ->where('y IN (?)', $ys);


        foreach ($this->selectAll($select) as $id) {
            $ids[] = $id['armyId'];
        }

        return $ids;
    }

    public function getArmiesFromCastlePosition($position)
    {
        $xs = array(
            $position['x'],
            $position['x'] + 1
        );

        $ys = array(
            $position['y'],
            $position['y'] + 1
        );

        $select = $this->_db->select()
            ->from($this->_name, array('armyId', 'x', 'y'))
            ->where('"gameId" = ?', $this->_gameId)
            ->where('destroyed = false')
            ->where('x IN (?)', $xs)
            ->where('y IN (?)', $ys);

        return $this->selectAll($select);
    }
}
