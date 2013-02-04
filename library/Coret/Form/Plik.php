<?php

class Coret_Form_Plik extends Zend_Form {

    public function init() {
        if (isset($this->_attribs['label'])) {
            $label = $this->_attribs['label'];
        }

        if (isset($this->_attribs['required']) && $this->_attribs['required']) {
            $label .= '*';
        }

        $this->addElement('file', 'file', array(
            'label' => $label,
            'required' => $this->_attribs['required'],
            'destination' => APPLICATION_PATH . '/../public/upload'
        ));
    }

}

