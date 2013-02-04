<?php

class Admin_Form_Album extends Zend_Form {

    public function init() {
        $this->setMethod('post');

        $this->addElement('text', 'title', array(
            'label' => 'Nazwa albumu:',
            'required' => true,
            'filters' => array('StringTrim'),
                )
        );

        $this->addElement('hidden', 'id');
        $this->addElement('submit', 'submit', array('label' => 'Potwierdź'));
    }

}

