<?php

class Application_Model_Ruin extends Game_Db_Table_Abstract {

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
                    ->where('"' . $this->_primary . '" = ?', $ruinId)
                    ->where('"gameId" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            if (isset($result[0][$this->_primary])) {
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

    public function getVisited() {
        try {
            $select = $this->_db->select()
                    ->from($this->_name, $this->_primary)
                    ->where('"gameId" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            $array = array();
            foreach ($result as $row) {
                $array[$row['ruinId']] = $row;
            }
            return $array;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function getFull() {
        try {
            $select = $this->_db->select()
                    ->from($this->_name, $this->_primary)
                    ->where('"gameId" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            $ruins = Application_Model_Board::getRuins();
            foreach ($result as $row) {
                if (isset($ruins[$row['ruinId']])) {
                    unset($ruins[$row['ruinId']]);
                }
            }
            return $ruins;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

    public function searchRuin($heroId, $armyId, $playerId) {
        new Game_Logger('HEROID: '.$heroId);
        $namespace = Game_Namespace::getNamespace();
        $modelGame = new Application_Model_Game($namespace->gameId);
        $modelArmy = new Application_Model_Army($namespace->gameId);
        $turn = $modelGame->getTurn();

        $random = rand(0, 100);
        if ($random < 10) {//10%
            //śmierć
            if ($turn['nr'] <= 7) {
                $find = array('null', 1);
            } else {
                $find = array('death', 1);
                $modelArmy->armyRemoveHero($heroId);
            }
        } elseif ($random < 55) {//45%
            //kasa
            $gold = rand(50, 150);
            $find = array('gold', $gold);
            $inGameGold = $modelGame->getPlayerInGameGold($playerId);
            $modelGame->updatePlayerInGameGold($playerId, $gold + $inGameGold);
            $modelArmy->zeroHeroMovesLeft($armyId, $heroId, $playerId);
        } elseif ($random < 85) {//30%
            //jednostki
            if ($turn['nr'] <= 7) {
                $max1 = 11;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turn['nr'] <= 12) {
                $max1 = 13;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turn['nr'] <= 16) {
                $max1 = 14;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turn['nr'] <= 19) {
                $max1 = 15;
                $min2 = 1;
                $max2 = 1;
            } elseif ($turn['nr'] <= 21) {
                $max1 = 15;
                $min2 = 1;
                $max2 = 2;
            } elseif ($turn['nr'] <= 23) {
                $max1 = 15;
                $min2 = 1;
                $max2 = 3;
            } elseif ($turn['nr'] <= 25) {
                $max1 = 15;
                $min2 = 2;
                $max2 = 3;
            } else {
                $max1 = 15;
                $min2 = 3;
                $max2 = 3;
            }
            $unitId = rand(11, $max1);
            $numerOfUnits = rand($min2, $max2);
            $find = array('alies', $numerOfUnits);
            for ($i = 0; $i < $numerOfUnits; $i++) {
                $modelArmy->addSoldierToArmy($armyId, $unitId, $playerId);
            }
            $modelArmy->zeroHeroMovesLeft($armyId, $heroId, $playerId);
        } elseif ($random < 95) {//10%
            //nic
            $find = array('null', 1);
            $modelArmy->zeroHeroMovesLeft($armyId, $heroId, $playerId);
        } else {//5%
            //artefakt
            $artefactId = rand(5, 34);
            $modelInventory = new Application_Model_Inventory($namespace->gameId);

            if ($modelInventory->itemExists($artefactId, $heroId)) {
                $modelInventory->increaseItemQuantity($artefactId, $heroId);
            } else {
                $modelInventory->addArtefact($artefactId, $heroId);
            }
            $find = array('artefact', $artefactId);
            $modelArmy->zeroHeroMovesLeft($armyId, $heroId, $playerId);
        }
        return $find;
    }

}

