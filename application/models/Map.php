<?php

class Application_Model_Map extends Game_Db_Table_Abstract
{
    protected $_name = 'map';
    protected $_primary = 'mapId';
    protected $_sequence = "map_mapId_seq";
    protected $_db;
    protected $mapId;
    
    public function __construct($mapId = 0) {
        $this->mapId = $mapId;
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function createMap($params, $playerId) {
        $data = array(
            'name' => $params['name'],
            'mapWidth' => $params['mapWidth'],
            'mapHeight' => $params['mapHeight'],
            'playerId' => $playerId
        );

        $this->_db->insert($this->_name, $data);
        $seq = $this->_db->quoteIdentifier($this->_sequence);
        return $this->_db->lastSequenceId($seq);
    }
}

