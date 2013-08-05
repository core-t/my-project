<?php

class Application_Model_RuinsInGame extends Game_Db_Table_Abstract
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
            'ruinId' => $ruinId,
            'gameId' => $this->_gameId
        );

        try {
            $this->_db->insert('ruin', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }


}

