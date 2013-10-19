<?php

class Admin_Model_Language extends Coret_Model_ParentDb
{

    protected $_name = 'language';
    protected $_primary = 'languageId';
    protected $_columns = array(
        'countryCode' => array('label' => 'Kod kraju', 'type' => 'varchar')
    );
    protected $_columns_lang = array(
        'name' => array('label' => 'Nazwa', 'type' => 'varchar')
    );

}

