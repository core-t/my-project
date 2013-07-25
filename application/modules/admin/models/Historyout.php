<?php

class Admin_Model_Historyout extends Coret_Model_ParentDb
{
    protected $_name = 'gamehistoryout';
    protected $_primary = 'gamehistoryoutId';
    protected $_columns = array(
        'gameId' => array('label' => 'Game Id', 'type' => 'varchar'),
        'data' => array('label' => 'Data', 'type' => 'text'),
        'type' => array('label' => 'Type', 'type' => 'varchar'),
        'date' => array('label' => 'Date', 'type' => 'date'),
        'attackerColor' => array('label' => 'A color', 'type' => 'varchar'),
        'attackerArmy' => array('label' => 'A army', 'type' => 'varchar'),
        'defenderColor' => array('label' => 'D color', 'type' => 'varchar'),
        'defenderArmy' => array('label' => 'D army', 'type' => 'varchar'),
        'path' => array('label' => 'Path', 'type' => 'varchar'),
        'battle' => array('label' => 'Battle', 'type' => 'varchar'),
        'oldArmyId' => array('label' => 'oldArmyId', 'type' => 'varchar'),
        'deletedIds' => array('label' => 'deletedIds', 'type' => 'varchar'),
        'victory' => array('label' => 'victory', 'type' => 'varchar'),
        'castleId' => array('label' => 'castleId', 'type' => 'varchar'),
        'ruinId' => array('label' => 'ruinId', 'type' => 'varchar'),
        'lost' => array('label' => 'lost', 'type' => 'varchar'),
        'win' => array('label' => 'win', 'type' => 'varchar'),
        'gold' => array('label' => 'gold', 'type' => 'varchar'),
        'costs' => array('label' => 'costs', 'type' => 'varchar'),
        'income' => array('label' => 'income', 'type' => 'varchar'),
        'armies' => array('label' => 'armies', 'type' => 'varchar'),
        'nr' => array('label' => 'nr', 'type' => 'varchar'),
        'action' => array('label' => 'action', 'type' => 'varchar'),
        'color' => array('label' => 'color', 'type' => 'varchar'),
        'x' => array('label' => 'x', 'type' => 'varchar'),
        'y' => array('label' => 'y', 'type' => 'varchar'),
    );
    protected $_columns_lang = array();
    protected $_order = 'data';
}