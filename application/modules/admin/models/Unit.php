<?php

class Admin_Model_Unit extends Coret_Model_ParentDb
{
    protected $_name = 'unit';
    protected $_primary = 'unitId';
    protected $_sequence = 'unit_unitId_seq';

    protected $_columns = array(
        'unitId' => array('label' => 'Unit ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'numberOfMoves' => array('label' => 'Ilość ruchów', 'type' => 'number'),
        'attackPoints' => array('label' => 'Punkty ataku', 'type' => 'number'),
        'defensePoints' => array('label' => 'Punkty obrony', 'type' => 'number'),
        'canFly' => array('label' => 'Lata', 'type' => 'checkbox'),
        'canSwim' => array('label' => 'Pływa', 'type' => 'checkbox'),
        'modMovesForest' => array('label' => 'Ruchy po lesie', 'type' => 'number'),
        'modMovesSwamp' => array('label' => 'Ruchy po bagnie', 'type' => 'number'),
        'modMovesHills' => array('label' => 'Ruchy po wzgórzach', 'type' => 'number'),
        'cost' => array('label' => 'Koszt utrzymania', 'type' => 'number'),
    );
    protected $_columns_lang = array(
        'name' => array('label' => 'Nazwa', 'type' => 'varchar')
    );

    public function getUnits()
    {
        $units = array(null);

        $select = $this->_db->select()
            ->from($this->_name, $this->_primary, null)
            ->where('id_lang = ?', Zend_Registry::get('config')->id_lang)
            ->join($this->_name . '_Lang', $this->_name . ' . ' . $this->_db->quoteIdentifier($this->_primary) . ' = ' . $this->_db->quoteIdentifier($this->_name . '_Lang') . ' . ' . $this->_db->quoteIdentifier($this->_primary), 'name')
            ->order(array('attackPoints', 'defensePoints', 'numberOfMoves')
            );

        foreach ($this->selectAll($select) as $unit) {
            $units[$unit[$this->_primary]] = $unit['name'];
        }

        return $units;
    }
}

