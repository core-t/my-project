<?php

class Application_Model_HeroesInGame extends Game_Db_Table_Abstract
{
    protected $_name = 'heroesingame';
    protected $_gameId;

    public function __construct($gameId, $db = null)
    {
        $this->gameId = $gameId;
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

        try {
            return $this->_db->insert($this->_name, $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    public function resetMovesLeft($playerId)
    {
    }


}

