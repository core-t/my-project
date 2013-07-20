<?php

class Coret_Form_Gallery extends Zend_Form
{
    public function init()
    {
        $this->setMethod('post');
        $this->setAttrib('enctype', 'multipart/form-data');

        $this->addElement('hidden', 'formName', array(
            'value' => 'gallery'
        ));

        $this->setAttrib('class', 'form2');
    }
}
