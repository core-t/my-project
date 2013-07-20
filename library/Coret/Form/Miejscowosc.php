<?php

class Coret_Form_Miejscowosc extends Zend_Form
{

    public function init()
    {
        $options = array();

        $mMiejscowosc = new Admin_Model_Miejscowosc(array('id_lang' => 1));

        foreach ($mMiejscowosc->getList(array('id_miejscowosc'), array('nazwa')) as $row) {
            $options[$row['id_miejscowosc']] = $row['nazwa'];
        }

        $this->_attribs['opt'] = $options;

        $f = new Coret_Form_Select($this->_attribs);
        $this->addElements($f->getElements());

    }

}

