<?php

class Admin_Form_Onas extends Zend_Form {

    public function init() {
        $this->setMethod('post');

        $this->addElement('textarea', 'content', array(
            'label' => 'Treść:',
            'required' => false,
            'cols' => '50',
            'rows' => '10',
            'filters' => array('StringTrim', 'StripTags')
                )
        );

        $lang = new Coret_Form_Lang();
        $this->addElements($lang->getElements());

        $this->addElement('hidden', 'id');

        $this->addElement('submit', 'submit', array('label' => 'Potwierdź'));
    }

}

