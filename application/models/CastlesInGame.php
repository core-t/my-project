<?php

class Application_Model_CastlesInGame extends Game_Db_Table_Abstract
{
    protected $_name = 'castlesingame';
    protected $_primary = array('castleId', 'gameId');
    protected $_sequence = '';
    protected $_castleId;
    protected $_gameId;

    public function __construct($db)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function setCastleProduction($unitId, $playerId)
    {
        $where = array(
            $this->_db->quoteInto('"gameId" = ?', $this->_gameId),
            $this->_db->quoteInto('"castleId" = ?', $this->_castleId),
            $this->_db->quoteInto('"playerId" = ?', $playerId)
        );


        $data = array(
            'production' => $unitId,
            'productionTurn' => 0
        );

        return $this->update($data, $where);
    }
}

