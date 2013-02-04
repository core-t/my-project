<?php

class Admin_Form_Portfolio extends Zend_Form {

    public function init() {
        $this->setMethod('post');
        $this->setAttrib('enctype', 'multipart/form-data');

        $this->addElement('file', 'image', array(
            'label' => 'Logo:',
            'required' => false,
            'destination' => APPLICATION_PATH . '/../public/upload',
            'validators' => array(array('Count', false, 1), array('Extension', false, 'jpg,png')),
        ));

        $this->addElement('text', 'title', array(
            'label' => 'Tytuł:',
            'required' => false,
            'filters' => array('StringTrim', 'StripTags'),
            'validators' => array(array('StringLength', false, array(0, 256))))
        );

        $this->addElement('text', 'info1', array(
            'label' => 'Typ:',
            'required' => false,
            'filters' => array('StringTrim', 'StripTags'),
            'validators' => array(array('StringLength', false, array(0, 256))))
        );

        $this->addElement('text', 'info2', array(
            'label' => 'Adres WWW:',
            'required' => false,
            'filters' => array('StringTrim', 'StripTags'),
            'validators' => array(array('StringLength', false, array(0, 256))))
        );

        $this->addElement('text', 'info3', array(
            'label' => 'Data:',
            'required' => false,
            'filters' => array('StringTrim', 'StripTags'),
            'validators' => array(array('StringLength', false, array(0, 256))))
        );

        $this->addElement('text', 'info4', array(
            'label' => 'Użyte technologie:',
            'required' => false,
            'filters' => array('StringTrim', 'StripTags'),
            'validators' => array(array('StringLength', false, array(0, 256))))
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

