<?php

class Admin_Model_Player extends Coret_Model_ParentDb
{

    protected $_name = 'player';
    protected $_primary = 'playerId';
    protected $_columns = array(
        'login' => array('label' => 'Login', 'typ' => 'varchar'),
        'activity' => array('label' => 'Aktywność', 'typ' => 'date'),
        'firstName' => array('label' => 'Imię', 'typ' => 'varchar'),
        'lastName' => array('label' => 'Nazwisko', 'typ' => 'varchar'),
        'admin' => array('label' => 'Admin', 'typ' => 'checkbox'),
//        'locale' => array('label' => 'Lang', 'typ' => 'varchar'),
        'creationDate' => array('label' => 'Data utworzenia', 'typ' => 'date')
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

}

