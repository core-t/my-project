<?php

class Admin_Form_Oferta extends Zend_Form {

    public function init() {
        $this->setMethod('post');
        $this->setAttrib('enctype', 'multipart/form-data');

        $this->addElement('text', 'title', array(
            'label' => 'Tytuł:',
            'required' => false,
            'filters' => array('StringTrim'),
            'validators' => array(array('StringLength', false, array(0, 256)))
                )
        );

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

