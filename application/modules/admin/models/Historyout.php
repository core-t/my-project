<?php

class Admin_Model_Historyout extends Coret_Model_ParentDb
{
    protected $_name = 'gamehistoryout';
    protected $_primary = 'gamehistoryoutId';
    protected $_columns = array(
        'gameId' => array('label' => 'Game Id', 'typ' => 'varchar'),
        'data' => array('label' => 'Data', 'typ' => 'text'),
        'type' => array('label' => 'Type', 'typ' => 'varchar'),
        'date' => array('label' => 'Date', 'typ' => 'date'),
        'attackerColor' => array('label' => 'A color', 'typ' => 'varchar'),
        'attackerArmy' => array('label' => 'A army', 'typ' => 'varchar'),
        'defenderColor' => array('label' => 'D color', 'typ' => 'varchar'),
        'defenderArmy' => array('label' => 'D army', 'typ' => 'varchar'),
        'path' => array('label' => 'Path', 'typ' => 'varchar'),
        'battle' => array('label' => 'Battle', 'typ' => 'varchar'),
    );
    protected $_columns_lang = array();
    protected $_order = 'data';
}