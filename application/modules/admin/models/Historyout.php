<?php

class Admin_Model_Historyout extends Coret_Model_ParentDb
{
    protected $_name = 'gamehistoryout';
    protected $_primary = 'gamehistoryoutId';
    protected $_columns = array(
        'gameId' => array('nazwa' => 'Game Id', 'typ' => 'varchar'),
        'data' => array('nazwa' => 'Dane', 'typ' => 'text'),
        'type' => array('nazwa' => 'Typ', 'typ' => 'varchar'),
        'date' => array('nazwa' => 'Data', 'typ' => 'date'),
    );
    protected $_columns_lang = array();
    protected $_order = 'data';
}