<?php

class Admin_Model_Language extends Coret_Model_ParentDb
{

    protected $_name = 'language';
    protected $_primary = 'languageId';
    protected $_sequence = 'language_languageId_seq';

    protected $_columns = array(
        'countryCode' => array('label' => 'Kod kraju', 'type' => 'varchar')
    );
    protected $_columns_lang = array(
        'name' => array('label' => 'Nazwa', 'type' => 'varchar')
    );

    public function getLanguageIdByCountryCode($countryCode)
    {
        $select = $this->_db->select()
            ->from($this->_name)
            ->where($this->_db->quoteIdentifier('countryCode') . ' = ?', $countryCode);

        return $this->_db->fetchOne($select);
    }
}

