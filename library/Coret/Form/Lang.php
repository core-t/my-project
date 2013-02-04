<?php

class Coret_Form_Lang extends Zend_Form {

    public function init() {
        $this->addElement('select', 'lang', array(
            'label' => 'Język:',
            'required' => false,
            'multiOptions' => array('de' => 'Niemiecki', 'en' => 'Angielski', 'pl' => 'Polski')
                )
        );
    }

}

