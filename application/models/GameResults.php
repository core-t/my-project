<?php

class Application_Model_GameResults extends Coret_Db_Table_Abstract
{
    protected $_name = 'gameresults';
    protected $_primary = 'gameresultsId';
    protected $_sequence = 'gameresults_gameresultsId_seq';
    protected $_gameId;

    public function __construct($gameId, $db = null)
    {
        $this->_gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function add($playerId, $castlesConquered, $castlesLost, $castlesDestroyed, $soldiersCreated, $soldiersKilled, $soldiersLost, $heroesKilled, $heroesLost, $gold, $soldiers, $heroes, $castles)
    {
        $data = array(
            'gameId' => $this->_gameId,
            'playerId' => $playerId,
            'castlesConquered' => $castlesConquered,
            'castlesLost' => $castlesLost,
            'castlesDestroyed' => $castlesDestroyed,
            'soldiersCreated' => $soldiersCreated,
            'soldiersKilled' => $soldiersKilled,
            'soldiersLost' => $soldiersLost,
            'heroesKilled' => $heroesKilled,
            'heroesLost' => $heroesLost,
            'gold' => $gold,
            'soldiers' => $soldiers,
            'heroes' => $heroes,
            'castles' => $castles,
        );

        $this->insert($data);
    }
}

