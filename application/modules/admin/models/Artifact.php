<?php

class Admin_Model_Artifact extends Coret_Model_ParentDb
{
    protected $_name = 'artifact';
    protected $_primary = 'artifactId';
    protected $_columns = array(
        'artifactId' => array('label' => 'Artifact ID', 'type' => 'number'),
        'name' => array('label' => 'Nazwa', 'type' => 'varchar'),
        'description' => array('label' => 'Opis', 'type' => 'varchar'),
        'image' => array('label' => 'Ikona', 'type' => 'image'),
        'probability' => array('label' => 'Prawdopodobieństwo', 'type' => 'number'),
        'canFly' => array('label' => 'Lata', 'type' => 'checkbox'),
        'canSwim' => array('label' => 'Pływa', 'type' => 'checkbox'),
    );

}

