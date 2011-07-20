
<?php

class Application_Form_Fbid extends Zend_Form {

    public function init () {
        /* Form Elements & Other Definitions Here ... */
        $this->setMethod ( 'post' );
        $this->setAction ( '/login' );
        $this->addElement ( 'text', 'fbid', array (
            'label' => 'Identyfikator FB',
            'required' => true,
            'filters' => array ( 'StringTrim' ),
            'validators' => array ( array ( 'StringLength', false, array ( 3, 3 ) ) )
                )
        );
        $this->addElement ( 'submit', 'submit', array ( 'label' => 'Zaloguj' ) );
    }

}

