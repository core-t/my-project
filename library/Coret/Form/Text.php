<?php

class Coret_Form_Text extends Zend_Form
{

    public function init()
    {
        $name = $this->_attribs['name'];
        if (isset($this->_attribs['label'])) {
            $label = $this->_attribs['label'];
        } else {
            $label = '';
        }
        if (isset($this->_attribs['value'])) {
            $value = $this->_attribs['value'];
        } else {
            $value = '';
        }

        if (isset($this->_attribs['required']) && $this->_attribs['required']) {
            $label .= '*';
            $required = $this->_attribs['required'];
        } else {
            $required = false;
        }

        if (isset($this->_attribs['validators']) && $this->_attribs['validators']) {
            $validators = $this->_attribs['validators'];
        } else {
            $validators = array();
        }

        $this->addElement('textarea', $name, array(
                'label' => $label,
                'required' => $required,
                'filters' => array('StringTrim'),
                'validators' => $validators,
                'value' => $value
            )
        );
    }

}

