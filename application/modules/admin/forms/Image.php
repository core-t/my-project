<?php

class Admin_Form_Image extends Zend_Form {

    public function init() {
        if (isset($this->_attribs['required']) && $this->_attribs['required']) {
            $label = 'Obrazek ' . $this->_attribs['numer'] . '*:';
        } else {
            $label = 'Obrazek ' . $this->_attribs['numer'] . ':';
        }

        if (isset($this->_attribs['numer']) && $this->_attribs['numer']) {
            $name = 'image' . $this->_attribs['numer'];
        } else {
            $name = 'image';
        }

        if (isset($this->_attribs['class']) && $this->_attribs['class']) {
            $class = $this->_attribs['class'];
        } else {
            $class = '';
        }

        $this->addElement('file', $name, array(
            'label' => $label,
            'class' => $class,
            'required' => $this->_attribs['required'],
            'destination' => APPLICATION_PATH . '/../public/upload',
            'validators' => array(array('Count', false, 1), array('Extension', false, 'jpg,png,gif')),
        ));
    }

}

