<?php

class Admin_Model_Mapterrain extends Coret_Model_ParentDb
{
    protected $_name = 'mapterrain';
    protected $_primary = 'mapTerrainId';
    protected $_sequence = 'mapterrain_mapTerrainId_seq';

    protected $_columns = array(
        'mapTerrainId' => array('label' => 'Terrain ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'mapId' => array('label' => 'Map ID', 'type' => 'number'),
        'type' => array('label' => 'Typ', 'type' => 'varchar'),
        'flying' => array('label' => 'Latanie', 'type' => 'number'),
        'swimming' => array('label' => 'Pływanie', 'type' => 'number'),
        'walking' => array('label' => 'Chodzenie', 'type' => 'number'),
    );
    protected $_columns_lang = array(
        'name' => array('label' => 'Nazwa', 'type' => 'varchar'),
//        'id_lang' => array('label' => 'Language ID', 'type' => 'number', 'active' => array('db' => true, 'form' => false, 'table' => false))
    );

    public function copy()
    {
        $select = $this->getSelect(array_keys($this->_columns), array_keys($this->_columns_lang));

//        Zend_Debug::dump($this->selectAll($select));
        foreach ($this->selectAll($select) as $v) {
            if ($v['id_lang'] == 2) {
                continue;
            }
            $data = array(
                'mapId' => 7,
                'type' => $v['type'],
                'flying' => $v['flying'],
                'swimming' => $v['swimming'],
                'walking' => $v['walking'],
                'name' => $v['name'],
                'id_lang' => $v['id_lang'],
            );
            $this->save($data);

        }
    }
}

