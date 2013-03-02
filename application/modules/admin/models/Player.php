<?php

class Admin_Model_Player extends Coret_Model_ParentDb
{

    protected $_name = 'player';
    protected $_primary = 'playerId';
    protected $_columns = array(
        'activity' => array('nazwa' => 'Aktywność', 'typ' => 'data'),
        'login' => array('nazwa' => 'Login', 'typ' => 'tekst'),
        'firstName' => array('nazwa' => 'Imię', 'typ' => 'tekst'),
        'lastName' => array('nazwa' => 'Nazwisko', 'typ' => 'tekst'),
    );

    public function __construct($params, $id = 0)
    {
        parent::__construct(array(), $id);
    }

    public function handleElement($post)
    {
        $dane = $this->prepareData($post);

        if ($post['id']) {
            $dane['password'] = md5($dane['password']);
            return $this->updateElement($dane);
        } else {
            $dane['password'] = md5($dane['password']);
            return $this->insertElement($dane);
        }
    }

    public function addSelectWhere($select)
    {
        $select = parent::addSelectWhere($select);
        $select->where($this->_name . '.computer = false');
        return $select;
    }

    protected function addSelectOrder($select)
    {
        $select = parent::addSelectOrder($select);
        return $select->order($this->_primary);
    }

    public function getPrimary()
    {
        return $this->_primary;
    }
}

