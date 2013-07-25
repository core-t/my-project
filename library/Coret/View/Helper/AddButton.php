<?php

class Zend_View_Helper_AddButton extends Zend_View_Helper_Abstract
{

    public function addButton()
    {
        $form = new Coret_Form_Search();
        return $form . '
<div class="pointer dodaj" onclick="document.location = \'' . $this->view->url(array('action' => 'add')) . '\'">
    <a class="button" href="' . $this->view->url(array('action' => 'add')) . '" title="Dodaj">Dodaj</a>
    <img src="' . $this->view->baseUrl() . '/img/admin/plus.png" alt="Dodaj" title="Dodaj"/>
</div>';

    }

}
