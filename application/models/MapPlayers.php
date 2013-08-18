<?php

class Application_Model_MapPlayers extends Game_Db_Table_Abstract
{
    protected $_name = 'mapplayers';
    protected $_primary = 'mapPlayerId';
    protected $_sequence = '';
    protected $mapId;

    public function __construct($mapId, $db = null)
    {
        $this->mapId = $mapId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getNumberOfPlayersForNewGame()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'count(*)')
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId);

        return $this->selectOne($select);
    }

    public function getColors()
    {
        $select = $this->_db->select()
            ->from($this->_name, 'shortName')
            ->where($this->_db->quoteIdentifier('mapId') . ' = ?', $this->mapId)
            ->order('startOrder');

        $all = $this->selectAll($select);

        $array = array();

        foreach ($all as $row) {
            $array[] = $row['shortName'];
        }

        return $array;
    }
}

