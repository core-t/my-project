<?php

class Coret_Form_Lang extends Zend_Form
{

    public function init()
    {
        $options = array();

        $language = new Coret_Model_Language(null);

        foreach ($language->getList() as $row) {
            if ($row['id_language'] == 1) {
                continue;
            }
            $options[$row['id_language']] = $row['skrot'];
        }

        $f = new Coret_Form_Select(array('name' => 'id_lang', 'label' => 'Język', 'opt' => $options));
        $this->addElements($f->getElements());

    }

}

