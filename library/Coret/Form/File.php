<?php

class Coret_Form_File extends Zend_Form
{

    public function init()
    {
        if (isset($this->_attribs['label'])) {
            $label = $this->_attribs['label'];
        } else {
            $label = '';
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
            $id = '';
        }

        if (isset($this->_attribs['validators']) && $this->_attribs['validators']) {
            $validators = $this->_attribs['validators'];
        } else {
            $validators = '';
        }

        $this->addElement('file', $this->_attribs['name'], array(
            'label' => $label,
            'class' => $class,
            'id' => $id,
            'required' => $required,
            'destination' => APPLICATION_PATH . '/../public/upload',
            'validators' => $validators
        ));
    }

}

