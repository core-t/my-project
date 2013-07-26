<?php

class Admin_Model_Map extends Coret_Model_ParentDb
{
    protected $_name = 'map';
    protected $_primary = 'mapId';
    protected $_columns = array(
        'mapId' => array('label' => 'Map ID', 'type' => 'number', 'active' => array('db' => false, 'form' => false)),
        'name' => array('label' => 'Nazwa', 'type' => 'varchar'),
        'mapWidth' => array('label' => 'Szerokość', 'type' => 'number'),
        'mapHeight' => array('label' => 'Wysokość', 'type' => 'number'),
        'date' => array('label' => 'Data', 'type' => 'date', 'active' => array('db' => false, 'form' => false)),
    );
    protected $_adminId = 'playerId';

    public function fields()
    {
        $fields = Application_Model_Board::getBoardFields();
        foreach ($fields as $y => $row) {
            foreach ($row as $x => $type) {
                $data = array(
                    'mapId' => 4,
                    'x' => $x,
                    'y' => $y,
                    'type' => $type
                );
                try {
                    $this->_db->insert('map', $data);
                } catch (Exception $e) {
                    echo $e;
                    exit;
                }
            }
        }
    }
}

