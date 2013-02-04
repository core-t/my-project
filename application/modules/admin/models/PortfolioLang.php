<?php

class Admin_Model_PortfolioLang extends Coret_Model_ParentDb {

    protected $_name = 'Portfolio_Lang';
    protected $_primary = 'id';
    protected $_columns = array(
        'title' => array('nazwa' => 'Tytuł', 'typ' => 'tekst'),
        'info1' => array('nazwa' => 'Typ', 'typ' => 'tekst'),
        'info2' => array('nazwa' => 'Adres WWW', 'typ' => 'tekst'),
        'info3' => array('nazwa' => 'Data', 'typ' => 'tekst'),
        'info4' => array('nazwa' => 'Użyte technologie', 'typ' => 'tekst'),
        'content' => array('nazwa' => 'Tekst', 'typ' => 'tekst'),
        'identyfikator' => array('nazwa' => 'Dodane przez', 'typ' => 'tekst'),
        'data' => array('nazwa' => 'Data utworzenia', 'typ' => 'data'),
        'lang' => array('nazwa' => 'Język', 'typ' => 'tekst')
    );

    /**
     *
     * @param type $dane
     * @return type
     */
    public function updateElement($dane) {
        $where = array(
            $this->_db->quoteInto('id_parent = ?', $dane['id_parent']),
            $this->_db->quoteInto('lang = ?', $dane['lang']),
        );

        $where = $this->addWhere($where);

        return $this->_db->update($this->_name, $dane, $where);
    }

    /**
     *
     * @param type $parentId
     * @return type
     */
    public function chechIfExist($params) {
        $select = $this->_db->select()
                ->from($this->_name, 'id')
                ->where('id_parent = ?', $params['id_parent'])
                ->where('lang = ?', $params['lang']);

        $select = $this->addSelectWhere($select);

        return $this->_db->fetchOne($select);
    }

}

