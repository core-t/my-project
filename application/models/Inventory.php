<?php

class Application_Model_Inventory extends Game_Db_Table_Abstract
{
    protected $_name = 'inventory';
    protected $_db;

    public function __construct($gameId) {
        $this->_gameId = $gameId;
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function addArtefact($artefactId, $heroId) {
        $data = array(
            'artefactId' => $artefactId,
            'gameId' => $this->_gameId,
            'heroId' => $heroId
        );
        $this->_db->insert($this->_name, $data);
    }

    public function itemExists($artefactId, $heroId){
        try {
            $select = $this->_db->select()
                ->from($this->_name, 'artefactId')
                ->where('"artefactId" = ?', $artefactId)
                ->where('"heroId" = ?', $heroId)
                ->where('"gameId" = ?', $this->_gameId);
            $result = $this->_db->query($select)->fetchAll();
            if(isset($result[0]['artefactId'])){
                return true;
            }
        } catch (PDOException $e) {
            throw new Exception($select->__toString());
        }
    }
    
    public function increaseItemQuantity($artefactId, $heroId){
        $data = array(
            'quantity' => new Zend_Db_Expr('quantity + 1')
        );
        $where = array(
            'artefactId' => $artefactId,
            'gameId' => $this->_gameId,
            'heroId' => $heroId
        );
        $this->_db->update($this->_name, $data, $where);
    }

}