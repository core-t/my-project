<?php

class Admin_Model_Player extends Coret_Model_ParentDb
{

    protected $_name = 'player';
    protected $_primary = 'playerId';
    protected $_columns = array(
        'login' => array('label' => 'Login', 'type' => 'varchar'),
        'password' => array('label' => 'Hasło', 'type' => 'password', 'active' => array('table' => false, 'form' => false)),
        'activity' => array('label' => 'Aktywność', 'type' => 'date', 'active' => array('form' => false)),
        'firstName' => array('label' => 'Imię', 'type' => 'varchar'),
        'lastName' => array('label' => 'Nazwisko', 'type' => 'varchar'),
        'admin' => array('label' => 'Admin', 'type' => 'checkbox'),
//        'locale' => array('label' => 'Lang', 'typ' => 'varchar'),
        'creationDate' => array('label' => 'Data utworzenia', 'type' => 'date', 'active' => array('form' => false))
    );

    public function addSelectWhere($select)
    {
        $select = parent::addSelectWhere($select);
        $select->where($this->_name . '.computer = false');

        return $select;
    }

    protected function addOrder($select)
    {
        $select = parent::addOrder($select);

        return $select->order('login');
    }

    public function getPrimaryIdByLogin($login)
    {
        $select = $this->_db->select()
            ->from($this->_name, $this->_primary)
            ->where('login = ?', $login);

        return $this->_db->fetchOne($select);
    }
}

