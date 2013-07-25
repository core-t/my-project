<?php

class Coret_Form_Number extends Zend_Form
{

    public function init()
    {

        if (isset($this->_attribs['validators']) && $this->_attribs['validators']) {
            $this->_attribs['validators'][] = array('Digit');
        } else {
            $this->_attribs['validators'] = array(array('Digit'));
        }

        $f = new Coret_Form_Varchar($this->_attribs);
        $this->addElements($f->getElements());
    }

}

