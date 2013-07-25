<?php

class Admin_Model_Player extends Coret_Model_ParentDb
{

    protected $_name = 'player';
    protected $_primary = 'playerId';
    protected $_columns = array(
        'login' => array('label' => 'Login', 'type' => 'varchar'),
        'activity' => array('label' => 'Aktywność', 'type' => 'date'),
        'firstName' => array('label' => 'Imię', 'type' => 'varchar'),
        'lastName' => array('label' => 'Nazwisko', 'type' => 'varchar'),
        'admin' => array('label' => 'Admin', 'type' => 'checkbox'),
//        'locale' => array('label' => 'Lang', 'typ' => 'varchar'),
        'creationDate' => array('label' => 'Data utworzenia', 'type' => 'date')
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

