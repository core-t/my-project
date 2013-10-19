<?php

class Coret_Form_Lang extends Zend_Form
{

    public function init()
    {
        $options = array();

        $language = new Admin_Model_Language(array());

        foreach ($language->getList() as $row) {
            if ($row['languageId'] == 1) {
                continue;
            }
            $options[$row['languageId']] = $row['countryCode'];
        }

        $f = new Coret_Form_Select(array('name' => 'id_lang', 'label' => 'Język', 'opt' => $options));
        $this->addElements($f->getElements());

    }

}

