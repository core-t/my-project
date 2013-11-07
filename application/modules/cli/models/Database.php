<?php

class Cli_Model_Database
{

    static public function getDb()
    {
        return new Zend_Db_Adapter_Pdo_Pgsql(array(
            'host' => Zend_Registry::get('config')->resources->db->params->host,
            'username' => Zend_Registry::get('config')->resources->db->params->username,
            'password' => Zend_Registry::get('config')->resources->db->params->password,
            'dbname' => Zend_Registry::get('config')->resources->db->params->dbname
        ));
    }

    static public function update($name, $data, $where, $db, $quiet = false)
    {
        try {
            $updateResult = $db->update($name, $data, $where);
        } catch (Exception $e) {
            echo($e);

            return;
        }
        switch ($updateResult) {
            case 1:
                return $updateResult;
                break;

            case 0:
                if ($quiet) {
                    return;
                }
                echo('
Zapytanie wykonane poprawnie lecz 0 rekordów zostało zaktualizowane
');
                Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
                break;

            case null:
                echo('
Zapytanie zwróciło błąd
');
                Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
                break;

            default:
                if ($quiet) {
                    return;
                }
                echo('
Został zaktualizowany więcej niż jeden rekord (' . $updateResult . ').
');
                Coret_Model_Logger::debug(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2));
                print_r($updateResult);
                break;
        }
    }

    static public function getEnemyArmiesFieldsPositions($gameId, $playerId, $db)
    {
        $fields = Zend_Registry::get('fields');

        $select = $db->select()
            ->from('army', array('x', 'y'))
            ->where('"gameId" = ?', $gameId)
            ->where('"playerId" != ?', $playerId)
            ->where('destroyed = false');

        try {
            $result = $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
            return;
        }

        foreach ($result as $row) {
            $fields[$row['y']][$row['x']] = 'e';
        }

        return $fields;
    }

    static public function getArmy($gameId, $armyId, $playerId, $db)
    {
        $select = $db->select()
            ->from('army', Cli_Model_Army::armyArray())
            ->where('"gameId" = ?', $gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false')
            ->where('"armyId" = ?', $armyId);

        try {
            $result = $db->fetchRow($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());

            return;
        }

        if (isset($result['armyId'])) {
            $mSoldier = new Application_Model_Soldier($gameId, $db);
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);

            $result['heroes'] = $mHeroesInGame->getArmyHeroes($armyId);

            foreach ($result['heroes'] as $k => $row) {
                $mInventory = new Application_Model_Inventory($row['heroId'], $gameId, $db);
                $result['heroes'][$k]['artifacts'] = $mInventory->getAll();
            }

            $result['soldiers'] = $mSoldier->getForWalk($armyId);
            $result['movesLeft'] = Cli_Model_Army::calculateArmyMovesLeft($result);
        }

        return $result;
    }

    static public function getArmyByArmyIdPlayerId($gameId, $armyId, $playerId, $db)
    {
        $select = $db->select()
            ->from('army', Cli_Model_Army::armyArray())
            ->where('"gameId" = ?', $gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false')
            ->where('"armyId" = ?', $armyId);
        try {
            $result = $db->fetchRow($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());

            return;
        }

        if (isset($result['armyId'])) {
            $mSoldier = new Application_Model_Soldier($gameId, $db);
            $mHeroesInGame = new Application_Model_HeroesInGame($gameId, $db);
            $result['heroes'] = $mHeroesInGame->getArmyHeroes($armyId);
            foreach ($result['heroes'] as $k => $row) {
                $mInventory = new Application_Model_Inventory($row['heroId'], $gameId, $db);
                $result['heroes'][$k]['artifacts'] = $mInventory->getAll();
            }
            $result['soldiers'] = $mSoldier->getForWalk($armyId);
            $result['movesLeft'] = Cli_Model_Army::calculateArmyMovesLeft($result);

            return $result;
        }

    }

    static private function getArmyHeroesForBattle($gameId, $ids, $db)
    {
        $select = $db->select()
            ->from(array('a' => 'hero'), array('attackPoints', 'defensePoints', 'name', 'heroId'))
            ->join(array('b' => 'heroesingame'), 'a."heroId" = b."heroId"', '')
            ->where('"gameId" = ?', $gameId)
            ->where('"armyId" IN (?)', $ids)
            ->order('attackPoints DESC', 'defensePoints DESC', 'numberOfMoves DESC');

        try {
            $result = $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());

            return;
        }

        foreach ($result as $k => $row) {
            $mInventory = new Application_Model_Inventory($row['heroId'], $gameId, $db);
            $result[$k]['artefacts'] = $mInventory->getAll();
//            $result[$k]['artefacts'] = self::getArtefactsByHeroId($gameId, $row['heroId'], $db);
        }

        return $result;
    }

    static public function getAllEnemyUnitsFromCastlePosition($gameId, $position, $db)
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
        $select = $db->select()
            ->from('army', 'armyId')
            ->where('"gameId" = ?', $gameId)
            ->where('destroyed = false')
            ->where('x IN (?)', $xs)
            ->where('y IN (?)', $ys);
        try {
            $result = $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }

        foreach ($result as $id) {
            $ids[] = $id['armyId'];
        }
        if ($ids) {
            $mSoldier = new Application_Model_Soldier($gameId, $db);
            return array(
                'heroes' => self::getArmyHeroesForBattle($gameId, $ids, $db),
                'soldiers' => $mSoldier->getForBattle($ids),
                'ids' => $ids
            );
        } else {
            return array(
                'heroes' => array(),
                'soldiers' => array(),
                'ids' => array()
            );
        }
    }

    static public function getAllEnemyUnitsFromPosition($gameId, $position, $playerId, $db)
    {
        $select = $db->select()
            ->from('army', 'armyId')
            ->where('"gameId" = ?', $gameId)
            ->where('"playerId" != ?', $playerId)
            ->where('destroyed = false')
            ->where('x = (?)', $position['x'])
            ->where('y = (?)', $position['y']);

        try {
            $result = $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());

            return;
        }

        $ids = array();

        foreach ($result as $id) {
            $ids[] = $id['armyId'];
        }

        if ($ids) {
            $mSoldier = new Application_Model_Soldier($gameId, $db);
            return array(
                'heroes' => self::getArmyHeroesForBattle($gameId, $ids, $db),
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

    static public function areMySwimmingUnitsAtPosition($gameId, $position, $playerId, $db)
    {
        $ids = '';
        $select = $db->select()
            ->from('army', 'armyId')
            ->where('"gameId" = ?', $gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false')
            ->where('x = (?)', $position['x'])
            ->where('y = (?)', $position['y']);
        try {
            $result = $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }

        foreach ($result as $id) {
            if ($ids) {
                $ids .= ',';
            }
            $ids .= $id['armyId'];
        }

        if (!$ids) {
            return;
        }

        $mSoldier = new Application_Model_Soldier($gameId, $db);

        return $mSoldier->getSwimmingFromArmiesIds($ids);
    }

    static public function getArmyPositionByArmyId($gameId, $armyId, $playerId, $db)
    {
        $select = $db->select()
            ->from('army', array('x', 'y'))
            ->where('"gameId" = ?', $gameId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false')
            ->where('"armyId" = ?', $armyId);
        try {
            return $db->fetchRow($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function areUnitsAtCastlePosition($gameId, $position, $db)
    {
        $xs = array(
            $position['x'],
            $position['x'] + 1
        );
        $ys = array(
            $position['y'],
            $position['y'] + 1
        );
        $select = $db->select()
            ->from('army', 'armyId')
            ->where('"gameId" = ?', $gameId)
            ->where('destroyed = false')
            ->where('x IN (?)', $xs)
            ->where('y IN (?)', $ys);
        try {
            return $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function getAllPlayerArmiesExeptOne($gameId, $armyId, $playerId, $db)
    {

        $select = $db->select()
            ->from('army', array('x', 'y'))
            ->where('"gameId" = ?', $gameId)
            ->where('"armyId" != ?', $armyId)
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false');
        try {
            return $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    static public function isComputer($playerId, $db)
    {
        $select = $db->select()
            ->from('player', 'computer')
            ->where('"playerId" = ?', $playerId);
        try {
            return $db->fetchOne($select);
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    /**
     * @param Zend_Db_Adapter_Pdo_Pgsql $db
     * @param int $gameId
     * @param int $playerId
     * @param string $data
     * @return mixed
     */
    static public function addTokensIn(Zend_Db_Adapter_Pdo_Pgsql $db, $gameId, $playerId, $token)
    {
        $data = array(
            'playerId' => $playerId,
            'gameId' => $gameId,
            'type' => $token['type']
        );

        unset($token['type']);

        $data['data'] = Zend_Json::encode($token);

        try {
            return $db->insert('tokensin', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    /**
     * @param Zend_Db_Adapter_Pdo_Pgsql $db
     * @param int $gameId
     * @param int $playerId
     * @param string $data
     * @return mixed
     */
    static public function addTokensOut(Zend_Db_Adapter_Pdo_Pgsql $db, $gameId, $token)
    {
        $data = array(
            'gameId' => $gameId,
            'type' => $token['type']
        );

        unset($token['type']);

        $keys = array(
            'attackerColor',
            'attackerArmy',
            'defenderColor',
            'defenderArmy',
            'path',
            'battle',
            'oldArmyId',
            'deletedIds',
            'victory',
            'castleId',
            'ruinId',
            'lost',
            'win',
            'gold',
            'costs',
            'income',
            'armies',
            'nr',
            'action',
            'color',
            'x',
            'y',
        );

        foreach ($keys as $key) {
            self::prepareGameHistoryData($key, $data, $token);
        }

        $data['data'] = Zend_Json::encode($token);

        try {
            return $db->insert('tokensout', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    static public function prepareGameHistoryData($value, &$data, &$token)
    {
        if (array_key_exists($value, $token)) {
            if (is_array($token[$value])) {
                $data[$value] = Zend_Json::encode($token[$value]);
            } elseif (is_bool($token[$value])) {
                if ($token[$value]) {
                    $data[$value] = 't';
                } else {
                    $data[$value] = 'f';
                }
            } else {
                $data[$value] = $token[$value];
            }

            unset($token[$value]);
        }
    }
}
