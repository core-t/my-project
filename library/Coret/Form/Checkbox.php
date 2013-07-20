<?php

class Coret_Form_Checkbox extends Zend_Form
{

    public function init()
    {
        $name = $this->_attribs['name'];
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

        $validators = array();
        if (isset($this->_attribs['validators']) && $this->_attribs['validators']) {
            foreach ($this->_attribs['validators'] as $val) {
                if (class_exists($val))
                    $validators[] = new $val();
            }
        }

        $this->addElement('checkbox', $name, array(
                'label' => $label,
                'required' => $required,
//                'filters' => array('StringTrim'),
                'validators' => $validators
            )
        );
    }

}

