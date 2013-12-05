<?php

class Application_Model_Hero extends Coret_Db_Table_Abstract
{
    protected $_name = 'hero';
    protected $_primary = 'heroId';
    protected $_sequence = 'hero_heroId_seq';
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

        return $this->_db->lastSequenceId($this->_db->quoteIdentifier($this->_sequence));
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

    public function isMyHero($heroId)
    {
        $select = $this->_db->select()
            ->from($this->_name, 'heroId')
            ->where($this->_db->quoteIdentifier('playerId') . ' = ?', $this->_playerId)
            ->where($this->_db->quoteIdentifier('heroId') . ' = ?', $heroId);

        return $this->selectOne($select);
    }
}

