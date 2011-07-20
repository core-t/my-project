<?php

class Application_Form_Registration extends Zend_Form {

    public function init () {
        
        $this->setMethod ( 'post' );
        $this->setAction ( '/registration' );
        $this->addElement ( 'text', 'userName', array (
            'label' => 'Login',
            'required' => true,
            'filters' => array ( 'StringTrim' ),
            'validators' => array ( array ( 'StringLength', false, array ( 4, 10 ) ) )
                )
        );
        $this->addElement( 'text', 'password', array(
            'label'=> 'Password',
            'required' => true,
            'validators' => array( array ( 'StringLength', false, array ( 4, 10 ) ) )
        ) );
        $this->addElement ( 'submit', 'submit', array ( 'label' => 'Register' ) );
    }

}