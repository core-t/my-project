<?php

class Application_Model_Hero extends Game_Db_Table_Abstract
{
    protected $_name = 'hero';
    protected $_primary = 'heroId';
    protected $_playerId;

    public function __construct($playerId, $db = null)
    {
        $this->_playerId = $playerId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function createHero()
    {
        $data = array(
            'playerId' => $this->_playerId
        );
        $this->insert($data);
    }

    public function getHeroes()
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where('"playerId" = ?', $this->_playerId);

        return $this->selectAll($select);
    }

    public function changeHeroName($heroId, $name)
    {
        $data['name'] = $name;
        $where = $this->_db->quoteInto('"' . $this->_primary . '" = ?', $heroId);
        return $this->_db->update($this->_name, $data, $where);
    }
}

