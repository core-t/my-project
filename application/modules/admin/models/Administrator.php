<?php

class Admin_Model_Administrator extends Coret_Model_ParentDb {

    protected $_name = 'Administrator';
    protected $_primary = 'id';
    protected $_columns = array(
        'identyfikator' => array('nazwa' => 'Identyfikator', 'typ' => 'tekst'),
        'data' => array('nazwa' => 'Data utworzenia', 'typ' => 'data')
    );

    public function __construct($params, $id = 0) {
        parent::__construct(array(), $id);
    }

    public function handleElement($post) {
        $dane = $this->prepareData($post);

        if ($post['id']) {
            $dane['haslo'] = md5($dane['haslo']);
            return $this->updateElement($dane);
        } else {
            $dane['haslo'] = md5($dane['haslo']);
            return $this->insertElement($dane);
        }
    }

}

