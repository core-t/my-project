<?php

class Admin_Form_Search extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
        $this->addElement('text', 'search', array(
                'label' => 'Szukaj',
                'required' => true,
                'filters' => array('StringTrim'),
                'validators' => array(array('StringLength', false, array(1, 256)))
            )
        );
    }

}

