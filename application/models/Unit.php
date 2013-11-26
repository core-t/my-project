<?php

class Application_Model_Unit extends Coret_Db_Table_Abstract
{

    protected $_name = 'unit';
    protected $_primary = 'unitId';

    public function __construct($db = null)
    {
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

}

