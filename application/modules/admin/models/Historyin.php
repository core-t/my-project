<?php

class Admin_Model_Historyin extends Coret_Model_ParentDb
{
    protected $_name = 'gamehistoryin';
    protected $_primary = 'gamehistoryinId';
    protected $_columns = array(
        'gameId' => array('label' => 'Game Id', 'type' => 'varchar'),
        'playerId' => array('label' => 'Player Id', 'type' => 'varchar'),
        'data' => array('label' => 'Data', 'type' => 'text'),
        'type' => array('label' => 'Type', 'type' => 'varchar'),
        'date' => array('label' => 'Date', 'type' => 'date'),
    );
    protected $_columns_lang = array();
    protected $_order = 'data';
}