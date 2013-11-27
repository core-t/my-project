<?php

class Application_Model_CastleProduction extends Coret_Db_Table_Abstract
{
    protected $_name = 'castleproduction';
    protected $_primary = 'castleProductionId';
    protected $_sequence = 'castleproduction_castleProductionId_seq';

    public function __construct($db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function getCastleProduction($castleId)
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier('castleId') . ' = ?', $castleId);

        $production = array();

        foreach ($this->selectAll($select) as $val) {
            $production[$val['unitId']] = $val;
        }

        return $production;
    }
}

