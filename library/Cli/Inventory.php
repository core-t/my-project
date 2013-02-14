<?php

class Cli_Inventory extends Game_Db_Table_Abstract {

    static public function addArtefact($gameId, $artefactId, $heroId, $db) {
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

    static public function itemExists($gameId, $artefactId, $heroId, $db) {
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

    static public function increaseItemQuantity($gameId, $artefactId, $heroId, $db) {
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