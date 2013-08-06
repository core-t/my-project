<?php

class Application_Model_Inventory extends Game_Db_Table_Abstract
{

    protected $_name = 'inventory';
    protected $_foreign_1 = 'artifactId';
    protected $_foreign_2 = 'heroId';
    protected $_foreign_3 = 'gameId';

    public function __construct($gameId, $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function addArtifact($artifactId, $heroId)
    {
        $data = array(
            $this->_db->quoteInto('"artifactId" = ?', $artifactId),
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"heroId" = ?', $heroId)
        );
        try {
            $this->_db->insert('inventory', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    public function itemExists($artifactId, $heroId)
    {
        $select = $this->_db->select()
            ->from('inventory', 'artifactId')
            ->where('"artifactId" = ?', $artifactId)
            ->where('"heroId" = ?', $heroId)
            ->where('"gameId" = ?', $this->_gameId);
        try {
            if ($this->_db->fetchOne($select) !== null) {
                return true;
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    public function getArtifactsByHeroId($heroId)
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_foreign_1)
            ->where($this->_db->quoteIdentifier($this->_foreign_2) . ' = ?', $heroId)
            ->where($this->_db->quoteIdentifier($this->_foreign_3) . ' = ?', $this->_gameId);

        try {
            return $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

}