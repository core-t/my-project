<?php

class Application_Model_MapCastlesProduction extends Game_Db_Table_Abstract
{
    protected $_name = 'mapcastlesproduction';
    protected $_primary = 'mapCastleProductionId';
    protected $_sequence = '';
    protected $_db;
    protected $mapCastleId;

    public function __construct()
    {
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function getMapCastles($mapCastleId)
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier('mapCastleId') . ' = ?', $mapCastleId);
        try {
            $production = $this->_db->query($select)->fetchAll();
        } catch (Exception $e) {
            throw new Exception($select->__toString());
        }

        return $production;
//        $mapCastles = array();
//
//        foreach ($castles as $val) {
//            $mapCastles[$val['mapCastleId']] = $val;
//        }
//
//        return $mapCastles;
    }

}

