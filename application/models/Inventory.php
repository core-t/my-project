<?php

class Application_Model_Inventory extends Game_Db_Table_Abstract
{
    protected $_name = 'inventory';
    protected $_foreign_1 = 'artifactId';
    protected $_foreign_2 = 'heroId';
    protected $_foreign_3 = 'gameId';

    public function __construct($heroId, $gameId, $db = null)
    {
        $this->_heroId = $heroId;
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function addArtifact($artifactId)
    {
        $data = array(
            $this->_db->quoteInto('"artifactId" = ?', $artifactId),
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"heroId" = ?', $this->_heroId)
        );


        $this->insert($data);
    }

    public function itemExists($artifactId)
    {
        $select = $this->_db->select()
            ->from('inventory', 'artifactId')
            ->where($this->_db->quoteIdentifier($this->_foreign_1) . ' = ?', $artifactId)
            ->where($this->_db->quoteIdentifier($this->_foreign_2) . ' = ?', $this->_heroId)
            ->where($this->_db->quoteIdentifier($this->_foreign_3) . ' = ?', $this->_gameId);

        if ($this->selectOne($select) !== null) {
            return true;
        }
    }

    public function getAll()
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_foreign_1)
            ->where($this->_db->quoteIdentifier($this->_foreign_2) . ' = ?', $this->_heroId)
            ->where($this->_db->quoteIdentifier($this->_foreign_3) . ' = ?', $this->_gameId);

        return $this->selectAll($select);
    }

}