<?php

class Application_Model_HeroesInGame extends Game_Db_Table_Abstract
{
    protected $_name = 'heroesingame';
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

    public function add($armyId, $heroId)
    {
        $data = array(
            'heroId' => $heroId,
            'armyId' => $armyId,
            'gameId' => $this->_gameId,
            'movesLeft' => 16
        );

        return $this->insert($data);
    }

    public function resetMovesLeft($playerId)
    {
    }


}

