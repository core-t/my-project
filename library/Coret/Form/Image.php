<?php

class Coret_Form_Image extends Coret_Form_File
{

    public function init()
    {
        $this->_attribs['validators'] = array(array('Count', false, 1), array('Extension', false, 'jpg,png,gif'));
        parent::init();
    }

}

