<?php

class Application_Model_Inventory extends Game_Db_Table_Abstract {

    protected $_name = 'inventory';
    protected $_db;

    public function __construct($gameId) {
        $this->_gameId = $gameId;
        $this->_db = $this->getDefaultAdapter();
        parent::__construct();
    }

    public function addArtefact($artefactId, $heroId) {
        $this->wsAddArtefact($this->_gameId, $artefactId, $heroId, $this->_db);
    }

    static public function wsAddArtefact($gameId, $artefactId, $heroId, $db) {
        $data = array(
            $db->quoteInto('"artefactId" = ?', $artefactId),
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"heroId" = ?', $heroId)
        );
        try {
            $db->insert('inventory', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    public function itemExists($artefactId, $heroId) {
        return $this->wsItemExists($this->_gameId, $artefactId, $heroId, $this->_db);
    }

    static public function wsItemExists($gameId, $artefactId, $heroId, $db) {
        $select = $db->select()
                ->from('inventory', 'artefactId')
                ->where('"artefactId" = ?', $artefactId)
                ->where('"heroId" = ?', $heroId)
                ->where('"gameId" = ?', $gameId);
        try {
            if ($db->fetchOne($select) !== null) {
                return true;
            }
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());
        }
    }

    public function increaseItemQuantity($artefactId, $heroId) {
        $this->wsIncreaseItemQuantity($this->_gameId, $artefactId, $heroId, $this->_db);
    }

    static public function wsIncreaseItemQuantity($gameId, $artefactId, $heroId, $db) {
        $data = array(
            'quantity' => new Zend_Db_Expr('quantity + 1')
        );
        $where = array(
            $db->quoteInto('"artefactId" = ?', $artefactId),
            $db->quoteInto('"gameId" = ?', $gameId),
            $db->quoteInto('"heroId" = ?', $heroId)
        );
        try {
            $db->update('inventory', $data, $where);
        } catch (Exception $e) {
            echo($e);
        }
    }

}