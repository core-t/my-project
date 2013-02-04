<?php

class Admin_Model_Onas extends Admin_Model_Cms {

    protected $_columns = array(
        'content' => array('nazwa' => 'Tekst', 'typ' => 'tekst'),
        'identyfikator' => array('nazwa' => 'Dodane przez', 'typ' => 'tekst'),
        'data' => array('nazwa' => 'Data utworzenia', 'typ' => 'data'),
        'lang' => array('nazwa' => 'Język', 'typ' => 'tekst')
    );

}

