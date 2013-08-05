<?php

class Application_Model_Inventory extends Game_Db_Table_Abstract
{

    protected $_name = 'inventory';

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

    public function increaseItemQuantity($artifactId, $heroId)
    {
        $data = array(
            'quantity' => new Zend_Db_Expr('quantity + 1')
        );
        $where = array(
            $this->_db->quoteInto('"artifactId" = ?', $artifactId),
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"heroId" = ?', $heroId)
        );
        try {
            $this->_db->update('inventory', $data, $where);
        } catch (Exception $e) {
            echo($e);
        }
    }

}