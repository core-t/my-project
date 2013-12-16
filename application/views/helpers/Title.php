<?php

class Zend_View_Helper_Title extends Zend_View_Helper_Abstract
{

    public function title()
    {
        $this->view->placeholder('title')->append('<div id="title"><div><h1>Wars of Fate</h1><p>strategic game</p></div></div>');
    }

}
