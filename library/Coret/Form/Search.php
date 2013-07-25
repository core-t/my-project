<?php

class Coret_Form_Search extends Zend_Form
{

    public function init()
    {
        $this->setMethod('post');
        $this->setName('search');

        $f = new Coret_Form_Varchar(array('name' => 'search', 'label' => '', 'validators' => array(array('StringLength', false, array(1, 256)))));
        $this->addElements($f->getElements());

        $f = new Coret_Form_Submit(array('name' => 'send', 'label' => 'Szukaj', 'class' => 'button'));
        $this->addElements($f->getElements());
    }

}

