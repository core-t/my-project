<?php

class Application_Model_Artefact extends Game_Db_Table_Abstract
{
    protected $_name = 'artefact';
    protected $_db;

    public function __construct() {
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function getArtefacts(){
        try {
            $select = $this->_db->select()
                ->from($this->_name);
            $result = $this->_db->query($select)->fetchAll();
            $artefacts = array();
            foreach($result as $k=>$row){
                $artefacts[$row['artefactId']] = $row;
            }
            return $artefacts;
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }

}