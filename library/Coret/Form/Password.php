<?php

class Coret_Form_Password extends Zend_Form
{

    public function init()
    {
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

        if (isset($this->_attribs['class']) && $this->_attribs['class']) {
            $class = $this->_attribs['class'];
        } else {
            $class = '';
        }

        if (isset($this->_attribs['id']) && $this->_attribs['id']) {
            $id = $this->_attribs['id'];
        } else {
            $id = $this->_attribs['name'];
        }

        if (isset($this->_attribs['validators']) && $this->_attribs['validators']) {
            $validators = $this->_attribs['validators'];
        } else {
            $validators = array(array('StringLength', false, array(1, 256)));
        }

        $this->addElement('password', $this->_attribs['name'], array(
                'label' => $label,
                'value' => $value,
                'class' => $class,
                'id' => $id,
                'required' => $required,
                'filters' => array('StringTrim'),
                'validators' => $validators
            )
        );
    }

}

