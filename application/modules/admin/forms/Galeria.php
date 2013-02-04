<?php

class Admin_Form_Galeria extends Zend_Form {

    public function init() {
        $this->setMethod('post');
        $this->setAttrib('enctype', 'multipart/form-data');

        $this->addElement('hidden', 'formName', array(
            'value' => 'galeria'
        ));

        $this->setAttrib('class', 'form2');
    }

}

