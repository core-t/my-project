<?php

class Coret_Form_Select extends Zend_Form
{

    public function init()
    {
        $name = $this->_attribs['name'];
        if (isset($this->_attribs['label'])) {
            $label = $this->_attribs['label'];
        } else {
            $label = '';
        }

        $options = $this->_attribs['opt'];

        if (isset($this->_attribs['required']) && $this->_attribs['required']) {
            $label .= '*';
            $required = $this->_attribs['required'];
        } else {
            $required = false;
        }

        $this->addElement('select', $name, array(
                'label' => $label,
                'required' => $required,
                'multiOptions' => $options
            )
        );
    }

}

