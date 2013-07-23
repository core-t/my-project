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
        'oldArmyId' => array('label' => 'oldArmyId', 'typ' => 'varchar'),
        'deletedIds' => array('label' => 'deletedIds', 'typ' => 'varchar'),
        'victory' => array('label' => 'victory', 'typ' => 'varchar'),
        'castleId' => array('label' => 'castleId', 'typ' => 'varchar'),
        'ruinId' => array('label' => 'ruinId', 'typ' => 'varchar'),
        'lost' => array('label' => 'lost', 'typ' => 'varchar'),
        'win' => array('label' => 'win', 'typ' => 'varchar'),
        'gold' => array('label' => 'gold', 'typ' => 'varchar'),
        'costs' => array('label' => 'costs', 'typ' => 'varchar'),
        'income' => array('label' => 'income', 'typ' => 'varchar'),
        'armies' => array('label' => 'armies', 'typ' => 'varchar'),
        'nr' => array('label' => 'nr', 'typ' => 'varchar'),
        'action' => array('label' => 'action', 'typ' => 'varchar'),
        'color' => array('label' => 'color', 'typ' => 'varchar'),
        'x' => array('label' => 'x', 'typ' => 'varchar'),
        'y' => array('label' => 'y', 'typ' => 'varchar'),
    );
    protected $_columns_lang = array();
    protected $_order = 'data';
}