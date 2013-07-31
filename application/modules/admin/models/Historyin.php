<?php

class Admin_Model_Historyin extends Coret_Model_ParentDb
{
    protected $_name = 'tokensin';
    protected $_primary = 'tokensinId';
    protected $_columns = array(
        'gameId' => array('label' => 'Game Id', 'type' => 'number'),
        'playerId' => array('label' => 'Player Id', 'type' => 'number'),
        'data' => array('label' => 'Data', 'type' => 'text'),
        'type' => array('label' => 'Type', 'type' => 'varchar'),
        'date' => array('label' => 'Date', 'type' => 'date'),
    );
    protected $_columns_lang = array();
    protected $_order = 'data';
}