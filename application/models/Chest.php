<?php

class Application_Model_Chest extends Game_Db_Table_Abstract
{

    protected $_name = 'chest';
    protected $_primary = 'chestId';
    protected $_foreign_1 = 'playerId';
    protected $_foreign_2 = 'artifactId';

    public function __construct($playerId, $db = null)
    {
        $this->_playerId = $playerId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function add($artifactId)
    {
        $data = array(
            $this->_foreign_2 => $artifactId,
            $this->_foreign_1 => $this->_playerId
        );

        try {
            $this->_db->insert($this->_name, $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    public function artifactExists($artifactId)
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_primary)
            ->where($this->_db->quoteIdentifier($this->_foreign_2) . ' = ?', $artifactId)
            ->where($this->_db->quoteIdentifier($this->_foreign_1) . ' = ?', $this->_playerId);

        try {
            return Zend_Validate::is($this->_db->fetchOne($select), 'Digits');
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    public function increaseArtifactQuantity($artifactId)
    {
        $data = array(
            'quantity' => new Zend_Db_Expr('quantity + 1')
        );

        $where = array(
            $this->_db->quoteInto($this->_db->quoteIdentifier($this->_foreign_2) . ' = ?', $artifactId),
            $this->_db->quoteInto($this->_db->quoteIdentifier($this->_foreign_1) . ' = ?', $this->_playerId)
        );

        try {
            $this->_db->update($this->_name, $data, $where);
        } catch (Exception $e) {
            echo($e);
        }
    }

    public function getAll()
    {
        $select = $this->_db->select()
            ->from($this->_name, array('artifactId', 'quantity'))
            ->where($this->_db->quoteIdentifier($this->_foreign_1) . ' = ?', $this->_playerId);

        $chest = array();

        foreach ($this->selectAll($select) as $row) {
            $chest[$row['artifactId']] = $row;
        }

        return $chest;
    }
}