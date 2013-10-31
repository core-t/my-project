<?php

class Application_Model_RuinsInGame extends Coret_Db_Table_Abstract
{

    protected $_name = 'ruinsingame';
    protected $_foreign_1 = 'gameId';
    protected $_foreign_2 = 'ruinId';

    public function __construct($gameId, $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getVisited()
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_foreign_2)
            ->where($this->_db->quoteIdentifier($this->_foreign_1) . ' = ?', $this->_gameId);

        try {
            $result = $this->_db->query($select)->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }

        $array = array();

        foreach ($result as $row) {
            $array[$row[$this->_foreign_2]] = $row;
        }

        return $array;
    }

    public function add($ruinId)
    {
        $data = array(
            $this->_foreign_2 => $ruinId,
            $this->_foreign_1 => $this->_gameId
        );

        try {
            $this->_db->insert($this->_name, $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    public function ruinExists($ruinId)
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_foreign_2)
            ->where($this->_db->quoteIdentifier($this->_foreign_2) . ' = ?', $ruinId)
            ->where($this->_db->quoteIdentifier($this->_foreign_1) . ' = ?', $this->_gameId);

        try {
            return Zend_Validate::is($this->_db->fetchOne($select), 'Digits');
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    public function getFullRuins()
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_foreign_2)
            ->where($this->_db->quoteIdentifier($this->_foreign_1) . ' = ?', $this->_gameId);
        try {
            $result = $this->_db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }

        $ruins = Zend_Registry::get('ruins');
        foreach ($result as $row) {
            if (isset($ruins[$row['ruinId']])) {
                unset($ruins[$row['ruinId']]);
            }
        }

        return $ruins;
    }
}

