<?php

class Application_Model_Soldier extends Game_Db_Table_Abstract
{
    protected $_name = 'soldier';
    protected $_primary = 'soldierId';
    protected $_gameId;

    public function __construct($gameId, $db = null)
    {
        $this->gameId = $gameId;
        if ($db) {
            $this->_db = $db;
        } else {
            parent::__construct();
        }
    }

    public function add($armyId, $unitId)
    {
        $units = Zend_Registry::get('units');

        $data = array(
            'armyId' => $armyId,
            'gameId' => $this->gameId,
            'unitId' => $unitId,
            'movesLeft' => $units[$unitId]['numberOfMoves']
        );

        try {
            return $this->db->insert('soldier', $data);
        } catch (Exception $e) {
            echo($e);
        }
    }

    public function resetMovesLeft($playerId)
    {
        $subSelect = $db->select()
            ->from('army', 'armyId')
            ->where('"playerId" = ?', $playerId)
            ->where('destroyed = false')
            ->where('"gameId" = ?', $gameId);

        $select = $db->select()
            ->from('soldier', array('movesLeft', 'soldierId', 'unitId'))
            ->where('"armyId" IN (?)', new Zend_Db_Expr($subSelect->__toString()))
            ->where('"gameId" = ?', $gameId);

        try {
            $soldiers = $db->query($select)->fetchAll();
        } catch (Exception $e) {
            echo($e);
            echo($select->__toString());

            return;
        }

        foreach ($soldiers as $soldier) {
            if ($soldier['movesLeft'] > 2) {
                $soldier['movesLeft'] = 2;
            }
            $select = $db->select()
                ->from('unit', new Zend_Db_Expr('"numberOfMoves" + ' . $soldier['movesLeft']))
                ->where('"unitId" = ?', $soldier['unitId']);
            $data = array(
                'movesLeft' => new Zend_Db_Expr('(' . $select->__toString() . ')')
            );
            $where = array(
                $db->quoteInto('"soldierId" = ?', $soldier['soldierId']),
                $db->quoteInto('"gameId" = ?', $gameId)
            );
            self::update('soldier', $data, $where, $db, true);
        }
    }


}

